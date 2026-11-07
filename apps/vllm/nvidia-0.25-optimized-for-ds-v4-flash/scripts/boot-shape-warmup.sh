#!/usr/bin/env bash
# boot-shape-warmup.sh — burn spec-decode/prefill Triton shape buckets at boot.
#
# Why (issue #117): under live traffic, shapes that the single smoke request
# never materializes JIT-compile mid-serve. jit_monitor warns about the latency
# spike, but the real hazard on TP=2 is worse: a rank stalled in compilation
# leaves its peer waiting in a collective, and torch's ProcessGroupNCCL
# watchdog (600 s, NOT covered by VLLM_EXECUTE_MODEL_TIMEOUT_SECONDS) kills
# the pair. Target kernel: _prepare_dflash_inputs_kernel. Its compile key is
#   BLOCK_SIZE = min(256, next_pow2(scheduled_tokens + 6))
# (+6 = 1 + num_speculative_tokens at the deployed num_speculative_tokens=5).
# Request concurrency does NOT enter this key at all, so the empirically live
# BLOCK keys {8, 16, 32, 64, 128, 256} are reached only through exact
# scheduled-token counts s with next_pow2(s+6) = B — never through chat-batch
# concurrency (all our chat prompts land BLOCK 256), and the longchunk tail
# does not help either (~1342 tokens -> BLOCK 256, not a low bucket).
#
# Two mechanisms:
# - Bucket ladder: six plain POST /v1/completions requests whose prompts are
#   built to encode exactly s tokens, s = {1, 6, 20, 45, 100, 200}, mapping via
#   next_pow2(s+6) onto every live BLOCK key {8,16,32,64,128,256}. The
#   deployed tokenizer encodes 'hello' + (s-1)x' hello' as exactly s tokens,
#   but that heuristic is never trusted blindly: each rung is verified at
#   runtime with an authenticated POST /tokenize BEFORE its completion fires,
#   and any count mismatch fails the rung — and the nonfatal warmup — with a
#   precise, secret-free diagnostic instead of silently warming a wrong shape.
# - Chat arms C=1/2/4/6 (bounded by the launcher's resolved
#   --max-num-seqs) cover both bounded longer prompts and ordinary short
#   requests with client-default generation settings. Medium + multi-chunk
#   long prefill and one thinking-off arm cover other batch-keyed variants;
#   none of these arms contributes to the low buckets above.
#
# Non-fatal by design: the cost of a missed shape is a mid-serve JIT (what this
# script exists to reduce), not an outage — the launcher must treat a warmup
# failure as WARN, never as a boot failure. Pair with a persistent
# TRITON_CACHE_DIR so each bucket is compiled once per image, not once per boot.
#
# Usage: boot-shape-warmup.sh [base_url] [model]
#   base_url default http://127.0.0.1:8000 ; model default deepseek-v4-flash-0731
# Env:
#   DSPARK_WARMUP_REQ_TIMEOUT  per-request curl --max-time for chat arms and
#                              ladder completions, seconds (default 240 —
#                              first-ever boot pays real compiles here)
#   DSPARK_WARMUP_BEARER       bearer handed over by the launcher (first parsed
#                              DSPARK_API_KEYS key, else VLLM_API_KEY); preferred
#                              over VLLM_API_KEY. Never logged by this script.
#   VLLM_API_KEY               added as Bearer auth when non-empty and no
#                              DSPARK_WARMUP_BEARER was provided
#   DSPARK_WARMUP_MAX_CONCURRENCY
#                              resolved --max-num-seqs from the launcher
#                              (default 6); C=1/2/4/6 arms above it are skipped
#   WARMUP_CURL                test seam: overrides the curl binary
set -u

BASE="${1:-http://127.0.0.1:8000}"
MODEL="${2:-deepseek-v4-flash-0731}"
CURL_BIN="${WARMUP_CURL:-curl}"
REQ_TIMEOUT="${DSPARK_WARMUP_REQ_TIMEOUT:-240}"
MAX_CONCURRENCY="${DSPARK_WARMUP_MAX_CONCURRENCY:-6}"
case "$MAX_CONCURRENCY" in
  ''|*[!0-9]*|0)
    echo "boot-shape-warmup: invalid DSPARK_WARMUP_MAX_CONCURRENCY=${MAX_CONCURRENCY@Q}; using 6" >&2
    MAX_CONCURRENCY=6
    ;;
esac
NONCE="$$-$(date +%s)"

AUTH_ARGS=()
if [ -n "${DSPARK_WARMUP_BEARER:-}" ]; then
  # Launcher-provided bearer wins: it is the same credential the smoke probe
  # authenticated with, so keyed clusters cannot 401 the whole sweep away.
  AUTH_ARGS=(-H "Authorization: Bearer ${DSPARK_WARMUP_BEARER}")
elif [ -n "${VLLM_API_KEY:-}" ]; then
  AUTH_ARGS=(-H "Authorization: Bearer ${VLLM_API_KEY}")
fi

# Deterministic bucket ladder: exact prompt-token counts -> live BLOCK keys.
LADDER_S=(1 6 20 45 100 200)    # next_pow2(s+6) = 8 16 32 64 128 256
next_pow2() { # smallest power of two >= $1
  local n=$1 p=1
  while [ "$p" -lt "$n" ]; do p=$((p * 2)); done
  printf '%s' "$p"
}

tmpdir="$(mktemp -d)"
trap 'rm -rf "$tmpdir"' EXIT

mk_prompt() { # $1 = approx token count (repeated filler words), $2 = tag
  local n=$1 tag=$2 body
  body=$(printf 'warm %.0s' $(seq 1 "$n"))
  printf '[warmup %s %s] The following is filler context, ignore it: %s Reply with OK.' \
    "$NONCE" "$tag" "$body"
}

fire() { # $1 = tag, $2 = words, $3 = thinking, $4 = result file, $5 = request profile
  local tag=$1 words=$2 thinking=$3 out=$4 profile=${5:-bounded} prompt payload
  prompt=$(mk_prompt "$words" "$tag")
  if [ "$profile" = "serve-default" ]; then
    # Mirror an ordinary short client request: no explicit max_tokens or
    # chat-template override. These scheduler defaults have distinct Triton
    # variants from the bounded long-context arms below.
    payload='{"model":"'"$MODEL"'","messages":[{"role":"user","content":"'"$prompt"'"}],"temperature":0}'
  else
    payload='{"model":"'"$MODEL"'","messages":[{"role":"user","content":"'"$prompt"'"}],"max_tokens":24,"temperature":0,"chat_template_kwargs":{"thinking":'"$thinking"',"reasoning_effort":"low"}}'
  fi
  if "$CURL_BIN" -fsS --max-time "$REQ_TIMEOUT" "${AUTH_ARGS[@]}" \
      "$BASE/v1/chat/completions" -H "Content-Type: application/json" \
      -d "$payload" >/dev/null 2>>"$tmpdir/errors"; then
    echo ok > "$out"
  else
    echo fail > "$out"
  fi
}

burst() { # $1 = arm name, $2 = concurrency, $3 = words-per-request, $4 = request profile
  local arm=$1 c=$2 words=$3 profile=${4:-bounded} i t0 t1
  # Pre-create every result file in the parent before forking so a subshell
  # that dies before writing still tallies as a failed outcome: the summary
  # can never claim n/n over fewer outcomes than requests scheduled.
  for i in $(seq 1 "$c"); do : > "$tmpdir/${arm}-${i}"; done
  t0=$(date +%s)
  for i in $(seq 1 "$c"); do
    fire "${arm}-${i}" "$words" true "$tmpdir/${arm}-${i}" "$profile" &
  done
  wait
  t1=$(date +%s)
  echo "  arm ${arm}: C=${c} x ~${words} tok, profile=${profile}, $((t1 - t0))s"
}

mk_ladder_prompt() { # $1 = exact token count ('hello' + (N-1)x ' hello')
  local n=$1 out="hello" i
  for ((i = 1; i < n; i++)); do out="$out hello"; done
  printf '%s' "$out"
}

verify_ladder_rung() { # $1 = exact token count; tokenize-gated completion
  local s=$1 prompt want_block got resp t0 t1
  : > "$tmpdir/ladder-$s"       # pre-created: counted even on failure paths
  prompt=$(mk_ladder_prompt "$s")
  want_block=$(next_pow2 $((s + 6)))
  # Runtime gate: never trust the word-count heuristic against the served
  # tokenizer. Authenticated POST /tokenize must confirm exactly s tokens
  # before this rung's completion may fire.
  if ! resp=$("$CURL_BIN" -fsS --max-time 30 "${AUTH_ARGS[@]}" \
        "$BASE/tokenize" -H "Content-Type: application/json" \
        -d '{"model":"'"$MODEL"'","prompt":"'"$prompt"'"}' \
        2>>"$tmpdir/errors"); then
    echo "boot-shape-warmup: tokenize verify FAILED for rung s=${s}: POST /tokenize errored — rung skipped, BLOCK ${want_block} NOT warmed" >&2
    echo fail > "$tmpdir/ladder-$s"
    return 0
  fi
  got=$(printf '%s\n' "$resp" | grep -o '"count"[[:space:]]*:[[:space:]]*[0-9]*' | head -n 1 | grep -o '[0-9]*$')
  if [ -z "$got" ]; then
    echo "boot-shape-warmup: tokenize verify FAILED for rung s=${s}: no usable \"count\" in /tokenize response — rung skipped, BLOCK ${want_block} NOT warmed" >&2
    echo fail > "$tmpdir/ladder-$s"
    return 0
  fi
  if [ "$got" -ne "$s" ]; then
    echo "boot-shape-warmup: tokenize verify FAILED for rung s=${s}: /tokenize reported ${got} tokens, need exactly ${s} — rung skipped, BLOCK ${want_block} NOT warmed" >&2
    echo fail > "$tmpdir/ladder-$s"
    return 0
  fi
  t0=$(date +%s)
  if "$CURL_BIN" -fsS --max-time "$REQ_TIMEOUT" "${AUTH_ARGS[@]}" \
      "$BASE/v1/completions" -H "Content-Type: application/json" \
      -d '{"model":"'"$MODEL"'","prompt":"'"$prompt"'","max_tokens":1,"temperature":0}' \
      >/dev/null 2>>"$tmpdir/errors"; then
    echo ok > "$tmpdir/ladder-$s"
    t1=$(date +%s)
    echo "  ladder s=${s}: tokenize ${got}/${s} -> BLOCK ${want_block} fired ($((t1 - t0))s)"
  else
    echo fail > "$tmpdir/ladder-$s"
    echo "  ladder s=${s}: tokenize ${got}/${s} -> BLOCK ${want_block} request FAILED"
  fi
}

ladder() {
  local s
  for s in "${LADDER_S[@]}"; do
    verify_ladder_rung "$s"
  done
}

if ! "$CURL_BIN" -fsS --max-time 10 "${AUTH_ARGS[@]}" "$BASE/v1/models" >/dev/null 2>&1; then
  echo "boot-shape-warmup: API not reachable at $BASE — skipping sweep" >&2
  exit 1
fi

echo "boot-shape-warmup: sweeping spec-decode/prefill shape buckets (issue #117)"
total_t0=$(date +%s)

# Kernel-critical first: deterministic bucket ladder (exact-token plain
# completions), then the batch/chat arms.
ladder

EXPECTED_CHAT_REQUESTS=5        # c1 + short-c1 + mid + longchunk + nothink
burst c1        1 300
burst short-c1  1 8 serve-default
if [ "$MAX_CONCURRENCY" -ge 2 ]; then burst c2 2 420; burst short-c2 2 8 serve-default; EXPECTED_CHAT_REQUESTS=$((EXPECTED_CHAT_REQUESTS + 4)); fi
if [ "$MAX_CONCURRENCY" -ge 4 ]; then burst c4 4 380; burst short-c4 4 8 serve-default; EXPECTED_CHAT_REQUESTS=$((EXPECTED_CHAT_REQUESTS + 8)); fi
if [ "$MAX_CONCURRENCY" -ge 6 ]; then burst c6 6 340; burst short-c6 6 8 serve-default; EXPECTED_CHAT_REQUESTS=$((EXPECTED_CHAT_REQUESTS + 12)); fi
if [ "$MAX_CONCURRENCY" -gt 6 ]; then
  echo "boot-shape-warmup: WARN: MAX_NUM_SEQS=${MAX_CONCURRENCY}; batch shapes above C=6 are not pre-warmed" >&2
fi
burst mid       1 2600
burst longchunk 1 9500          # crosses the 8192-token chunk boundary (BLOCK 256); its tail does NOT reach low buckets
t0=$(date +%s)
: > "$tmpdir/nothink-1"          # pre-created: counted even on subshell death
fire nothink-1 300 false "$tmpdir/nothink-1"
t1=$(date +%s)
echo "  arm nothink: C=1 x ~300 tok, thinking=false, $((t1 - t0))s"

total=0 ok_count=0
for f in "$tmpdir"/*-*; do
  [ -f "$f" ] || continue
  total=$((total + 1))
  [ "$(cat "$f")" = "ok" ] && ok_count=$((ok_count + 1))
done
EXPECTED_REQUESTS=$(( ${#LADDER_S[@]} + EXPECTED_CHAT_REQUESTS ))
if [ "$total" -ne "$EXPECTED_REQUESTS" ]; then
  echo "boot-shape-warmup: internal error: tallied $total outcomes for $EXPECTED_REQUESTS scheduled requests" >&2
  exit 1
fi
total_t1=$(date +%s)
echo "boot-shape-warmup: ${ok_count}/${total} requests ok in $((total_t1 - total_t0))s"

if [ "$ok_count" -lt "$total" ]; then
  echo "boot-shape-warmup: $((total - ok_count)) request(s) failed — uncovered shapes may JIT mid-serve" >&2
  sed -n '1,5p' "$tmpdir/errors" >&2 2>/dev/null || true
  exit 1
fi
exit 0
