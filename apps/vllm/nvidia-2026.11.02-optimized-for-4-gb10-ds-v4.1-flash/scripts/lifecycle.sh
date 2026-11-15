#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
STATE_DIR="$APP_DIR/.runtime"
DEPLOYED_DIR="$STATE_DIR/deployed"
mkdir -p "$STATE_DIR"
exec 9>"$STATE_DIR/lifecycle.lock"
flock -n 9 || { echo "Another lifecycle operation is running." >&2; exit 1; }
export ENV_FILE="$APP_DIR/.env"
export COMPOSE_FILE="$APP_DIR/docker-compose.yml"
if [[ "${1:-}" = stop || "${1:-}" = uninstall ]] && [ -f "$DEPLOYED_DIR/identity.sh" ]; then
  source "$DEPLOYED_DIR/identity.sh"
else
  source "$APP_DIR/scripts/config.sh"
  IFS=, read -r -a NODES <<< "$CLUSTER_NODES"
  if [ "${#NODES[@]}" -ne 4 ] || [ "${NODES[0]}" != "$LOCAL_IP" ]; then
    echo "Configure four CLUSTER_NODES with LOCAL_IP first." >&2
    exit 1
  fi
  for ((rank=0; rank<4; rank++)); do
    for ((previous=0; previous<rank; previous++)); do
      [ "${NODES[rank]}" != "${NODES[previous]}" ] || { echo "Cluster node addresses must be unique." >&2; exit 1; }
    done
  done
  PROJECT_NAME="$(printf '%s' "${PROJECT_NAME:-$CONTAINER_NAME}" | tr '[:upper:].' '[:lower:]-')"
  [[ "$PROJECT_NAME" =~ ^[a-z0-9][a-z0-9_-]*$ ]] || { echo "Invalid Compose project name." >&2; exit 1; }
  REMOTE_DIR="${WORKER_DIR:-/opt/1pds41}/$PROJECT_NAME"
fi
HEAD_ENV_FILE=""
SSH_OPTIONS=(-n -o BatchMode=yes -o ConnectTimeout=10 -o ServerAliveInterval=5 -o ServerAliveCountMax=3)
START_OWNED=0
OPERATION_DEADLINE=0

with_deployed_config() {
  local -a NODES=("${NODES[@]}")
  local PROJECT_NAME="$PROJECT_NAME" REMOTE_DIR="$REMOTE_DIR" CONTAINER_NAME="$CONTAINER_NAME"
  local COMPOSE_FILE="$COMPOSE_FILE" HEAD_ENV_FILE="${HEAD_ENV_FILE:-}"
  if [ -f "$DEPLOYED_DIR/identity.sh" ]; then
    source "$DEPLOYED_DIR/identity.sh"
    COMPOSE_FILE="$DEPLOYED_DIR/docker-compose.yml"
    HEAD_ENV_FILE="$DEPLOYED_DIR/.env"
  fi
  "$@"
}

save_deployment() {
  local encoded_nodes
  mkdir -p -m 0700 "$DEPLOYED_DIR" || return 1
  install -m 0600 "$STATE_DIR/0/.env" "$DEPLOYED_DIR/.env" || return 1
  install -m 0600 "$APP_DIR/container.env" "$DEPLOYED_DIR/container.env" || return 1
  install -m 0600 "$COMPOSE_FILE" "$DEPLOYED_DIR/docker-compose.yml" || return 1
  printf -v encoded_nodes '%q ' "${NODES[@]}"
  (
    umask 077
    printf 'NODES=(%s)\nPROJECT_NAME=%q\nREMOTE_DIR=%q\nCONTAINER_NAME=%q\n' \
      "$encoded_nodes" "$PROJECT_NAME" "$REMOTE_DIR" "$CONTAINER_NAME" > "$DEPLOYED_DIR/identity.sh.new"
  ) || return 1
  mv "$DEPLOYED_DIR/identity.sh.new" "$DEPLOYED_DIR/identity.sh"
}

run_node() {
  local rank="$1" duration="$2" remote remaining
  shift 2
  if [ "$OPERATION_DEADLINE" -gt 0 ]; then
    remaining=$((OPERATION_DEADLINE - SECONDS))
    if [ "$remaining" -le 0 ]; then
      echo "Timed out during cluster operation." >&2
      return 1
    fi
    if [ "$remaining" -lt "${duration%s}" ]; then
      duration="${remaining}s"
    fi
  fi
  if [ "$rank" -eq 0 ]; then
    timeout --kill-after=10s "$duration" "$@"
  else
    printf -v remote '%q ' "$@"
    timeout --kill-after=10s "$duration" ssh "${SSH_OPTIONS[@]}" "${NODES[rank]}" "$remote"
  fi
}

compose_node() {
  local rank="$1" directory env_file
  shift
  if [ "$rank" -eq 0 ]; then
    directory="$(dirname "$COMPOSE_FILE")"
    env_file="${HEAD_ENV_FILE:-$STATE_DIR/0/.env}"
    [ -f "$env_file" ] || env_file="$ENV_FILE"
  else
    directory="$REMOTE_DIR"
    env_file="$directory/.env"
  fi
  run_node "$rank" 120s env -u NODE_RANK -u HEADLESS -u MASTER_ADDR -u MASTER_PORT \
    -u HF_CACHE -u VLLM_CACHE -u FLASHINFER_CACHE -u B12X_CACHE -u TRITON_CACHE -u TILELANG_CACHE \
    -u DSPARK_VLLM_IMAGE -u IMAGE_PULL_POLICY -u CONTAINER_NAME -u DSPARK_MODEL_HOST -u DSPARK_MODEL_CONTAINER -u VLLM_SPARK_NOFILE_LIMIT \
    docker compose --project-name "$PROJECT_NAME" --env-file "$env_file" -f "$directory/docker-compose.yml" "$@"
}

check_stopped() {
  local rank names failed=0
  for ((rank=0; rank<4; rank++)); do
    if ! names="$(run_node "$rank" 120s docker ps --format '{{.Names}}')"; then
      echo "Cannot query Docker on ${NODES[rank]}." >&2
      failed=1
    elif printf '%s\n' "$names" | grep -Fxq "$CONTAINER_NAME"; then
      echo "Container $CONTAINER_NAME is still running on ${NODES[rank]}." >&2
      failed=1
    fi
  done
  return "$failed"
}

stop_cluster() {
  local rank failed=0
  for ((rank=0; rank<4; rank++)); do
    if [ "$rank" -eq 0 ]; then
      compose_node "$rank" down || failed=1
    else
      run_node "$rank" 120s bash -c '
        if [ -f "$1/docker-compose.yml" ] && [ -f "$1/.env" ]; then
          docker compose --project-name "$2" --env-file "$1/.env" -f "$1/docker-compose.yml" down
        else
          names=$(docker ps --format "{{.Names}}") || exit 1
          if printf "%s\n" "$names" | grep -Fxq "$3"; then
            echo "Cannot stop existing container without this application Compose files." >&2
            exit 1
          fi
        fi
      ' bash "$REMOTE_DIR" "$PROJECT_NAME" "$CONTAINER_NAME" || failed=1
    fi
  done
  check_stopped || failed=1
  return "$failed"
}

uninstall_cluster() {
  local rank
  stop_cluster || return 1
  for rank in 1 2 3; do
    run_node "$rank" 120s bash -c 'if [ -d "$1" ]; then cd "$1" && rm -f docker-compose.yml .env container.env docker-compose.yml.new .env.new container.env.new; fi' bash "$REMOTE_DIR" || return 1
  done
}

print_logs() {
  local rank
  for ((rank=0; rank<4; rank++)); do
    echo "Recent logs from ${NODES[rank]}:" >&2
    run_node "$rank" 10s docker logs --tail 60 "$CONTAINER_NAME" >&2 || true
  done
}

finish_start() {
  local status=$?
  trap - EXIT INT TERM
  if [ "$status" -ne 0 ] && [ "$START_OWNED" -eq 1 ]; then
    OPERATION_DEADLINE=$((SECONDS + 600))
    with_deployed_config print_logs
    with_deployed_config stop_cluster || echo "Cleanup failed; check all four nodes." >&2
  fi
  exit "$status"
}

prepare_cluster() {
  local rank inspect fingerprint head_fingerprint="" staging target source_file
  if [ -f "$DEPLOYED_DIR/identity.sh" ]; then
    with_deployed_config check_stopped || return 1
  fi
  check_stopped || return 1
  for ((rank=0; rank<4; rank++)); do
    python3 "$APP_DIR/scripts/compose-env.py" "$APP_DIR" "$STATE_DIR/$rank" "$rank" "${NODES[rank]}" || return 1
  done
  for ((rank=0; rank<4; rank++)); do
    run_node "$rank" 120s docker compose version >/dev/null || return 1
    run_node "$rank" 120s test -d "$DSPARK_MODEL_HOST" || {
      echo "Missing model directory on ${NODES[rank]}: $DSPARK_MODEL_HOST" >&2
      return 1
    }
    echo "Preparing image $DSPARK_VLLM_IMAGE on ${NODES[rank]} (policy: $IMAGE_PULL_POLICY)."
    case "$IMAGE_PULL_POLICY" in
      always) run_node "$rank" 2700s docker pull "$DSPARK_VLLM_IMAGE" || return 1 ;;
      if_not_present)
        if ! run_node "$rank" 120s docker image inspect "$DSPARK_VLLM_IMAGE" >/dev/null 2>&1; then
          run_node "$rank" 2700s docker pull "$DSPARK_VLLM_IMAGE" || return 1
        fi
        ;;
      never) ;;
      *) echo "Unsupported IMAGE_PULL_POLICY: $IMAGE_PULL_POLICY" >&2; return 1 ;;
    esac
    inspect="$(run_node "$rank" 120s docker image inspect "$DSPARK_VLLM_IMAGE")" || return 1
    fingerprint="$(printf '%s' "$inspect" | python3 "$APP_DIR/docker/image_identity.py")" || return 1
    if [ "$rank" -eq 0 ]; then
      head_fingerprint="$fingerprint"
    elif [ "$fingerprint" != "$head_fingerprint" ]; then
      echo "Image content differs on ${NODES[rank]}: $DSPARK_VLLM_IMAGE" >&2
      return 1
    fi
  done
  python3 -c 'import socket,sys; sock=socket.socket(); sock.setsockopt(socket.SOL_SOCKET,socket.SO_REUSEADDR,1); sock.bind(("0.0.0.0",int(sys.argv[1]))); sock.close()' "$VLLM_PORT" || return 1
  for ((rank=0; rank<4; rank++)); do
    staging="$STATE_DIR/$rank"
    run_node "$rank" 120s mkdir -p "${HF_HOME:-$HOME/.cache/huggingface}" "$HOME/.cache/vllm" "$HOME/.cache/flashinfer" "$HOME/.cache/b12x" "$HOME/.triton" "$HOME/.tilelang" || return 1
    if [ "$rank" -eq 0 ]; then
      install -m 0600 "$staging/container.env" "$APP_DIR/container.env" || return 1
    else
      run_node "$rank" 120s mkdir -p "$REMOTE_DIR" || return 1
      for target in docker-compose.yml .env container.env; do
        if [ "$target" = docker-compose.yml ]; then
          source_file="$COMPOSE_FILE"
        else
          source_file="$staging/$target"
        fi
        run_node 0 120s scp -q -o BatchMode=yes -o ConnectTimeout=10 "$source_file" "${NODES[rank]}:$REMOTE_DIR/$target.new" || return 1
      done
      run_node "$rank" 120s bash -c 'cd "$1" && chmod 600 .env.new container.env.new && mv docker-compose.yml.new docker-compose.yml && mv .env.new .env && mv container.env.new container.env' bash "$REMOTE_DIR" || return 1
    fi
    compose_node "$rank" config --quiet || return 1
  done
}

start_cluster() {
  local rank running
  if [ "$OPERATION_DEADLINE" -eq 0 ]; then
    OPERATION_DEADLINE=$((SECONDS + 2700))
  fi
  trap finish_start EXIT
  trap 'exit 130' INT
  trap 'exit 143' TERM
  prepare_cluster || return 1
  save_deployment || return 1
  START_OWNED=1
  for rank in 1 2 3 0; do
    compose_node "$rank" up -d --pull never || return 1
  done
  echo "Waiting for DeepSeek-V4.1-Flash on port $VLLM_PORT."
  while ((SECONDS < OPERATION_DEADLINE)); do
    for ((rank=0; rank<4; rank++)); do
      running="$(run_node "$rank" 120s docker inspect --format '{{.State.Running}}' "$CONTAINER_NAME")" || return 1
      if [ "$running" != true ]; then
        echo "Container exited on ${NODES[rank]}." >&2
        return 1
      fi
    done
    if curl --noproxy '*' -fsS --connect-timeout 1 --max-time 2 "http://127.0.0.1:$VLLM_PORT/health" >/dev/null 2>&1; then
      trap - EXIT INT TERM
      OPERATION_DEADLINE=0
      echo "DeepSeek-V4.1-Flash is ready."
      return 0
    fi
    sleep 5
  done
  echo "Timed out waiting for DeepSeek-V4.1-Flash." >&2
  return 1
}

case "${1:-}" in
  start) start_cluster ;;
  stop)
    OPERATION_DEADLINE=$((SECONDS + 480))
    with_deployed_config stop_cluster
    ;;
  restart)
    OPERATION_DEADLINE=$((SECONDS + 2700))
    with_deployed_config stop_cluster || exit 1
    start_cluster
    ;;
  uninstall)
    OPERATION_DEADLINE=$((SECONDS + 480))
    with_deployed_config uninstall_cluster
    ;;
  *) echo "Usage: $0 start|stop|restart|uninstall" >&2; exit 2 ;;
esac
