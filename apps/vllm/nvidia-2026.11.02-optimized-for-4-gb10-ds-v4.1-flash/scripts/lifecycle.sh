#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
source "$APP_DIR/scripts/config.sh"

IFS=, read -r -a NODES <<< "$CLUSTER_NODES"
if [ "${#NODES[@]}" -ne 4 ] || [ "${NODES[0]}" != "$LOCAL_IP" ]; then
  echo "Configure four CLUSTER_NODES with LOCAL_IP as the first (head) node." >&2
  exit 1
fi

mkdir -p "$APP_DIR/.runtime" "$APP_DIR/logs"
PID_FILE="$APP_DIR/.runtime/launcher.pid"
LOG_FILE="$APP_DIR/logs/appliance.log"
BOOT_ID="$(cat /proc/sys/kernel/random/boot_id)"
exec 9>"$APP_DIR/.runtime/lifecycle.lock"
flock -n 9 || { echo "Another lifecycle operation is running." >&2; exit 1; }

launcher_running() {
  local pid started boot current
  [ -f "$PID_FILE" ] || return 1
  read -r pid started boot < "$PID_FILE" || return 1
  [[ "$pid" =~ ^[0-9]+$ ]] && [ "$boot" = "$BOOT_ID" ] || return 1
  current="$(awk '{sub(/.*\) /, ""); if ($1 != "Z") print $20}' "/proc/$pid/stat" 2>/dev/null)" || return 1
  [ -n "$current" ] && [ "$current" = "$started" ]
}

check_cluster_stopped() {
  local node names failed=0
  for node in "${NODES[@]}"; do
    if [ "$node" = "$LOCAL_IP" ]; then
      if ! names="$(timeout --kill-after=5s 20s docker ps --format '{{.Names}}')"; then
        echo "Cannot query Docker on head $node." >&2
        failed=1
        continue
      fi
    elif ! names="$(timeout --kill-after=5s 20s ssh -n -o BatchMode=yes -o ConnectTimeout=10 -o ServerAliveInterval=5 -o ServerAliveCountMax=3 "$node" "docker ps --format '{{.Names}}'")"; then
      echo "Cannot query Docker on worker $node." >&2
      failed=1
      continue
    fi
    if printf '%s\n' "$names" | grep -Fxq "$CONTAINER_NAME"; then
      echo "Container $CONTAINER_NAME is still running on $node." >&2
      failed=1
    fi
  done
  return "$failed"
}

stop_cluster() {
  local attempt pid started boot group
  if launcher_running; then
    read -r pid started boot < "$PID_FILE"
    group="$(awk '{sub(/.*\) /, ""); print $3}' "/proc/$pid/stat" 2>/dev/null)" || group=""
    if launcher_running; then
      if [ "$group" != "$pid" ]; then
        echo "Launcher $pid has no independent process group; cannot stop it safely." >&2
        return 1
      fi
      kill -TERM -- "-$pid" 2>/dev/null || true
      for ((attempt=0; attempt<30; attempt++)); do
        launcher_running || break
        sleep 1
      done
      if launcher_running; then
        kill -KILL -- "-$pid" 2>/dev/null || true
      fi
    fi
  fi
  timeout --kill-after=10s 120s bash "$APP_DIR/appliance/stop.sh" || {
    echo "Appliance stop failed or timed out; check all four nodes." >&2
    return 1
  }
  check_cluster_stopped || return 1
  for ((attempt=0; attempt<60; attempt++)); do
    if ! launcher_running; then
      rm -f "$PID_FILE"
      return 0
    fi
    sleep 1
  done
  echo "The previous launcher has not exited; refusing to start another." >&2
  return 1
}

start_cluster() {
  local pid started deadline
  if launcher_running; then
    echo "The appliance launcher is already running." >&2
    return 1
  fi
  check_cluster_stopped || return 1
  python3 -c 'import socket, sys; sock = socket.socket(); sock.bind(("0.0.0.0", int(sys.argv[1]))); sock.close()' "$VLLM_PORT" 2>/dev/null || {
    echo "Port $VLLM_PORT is already in use." >&2
    return 1
  }

  nohup setsid bash "$APP_DIR/appliance/start.sh" </dev/null >>"$LOG_FILE" 2>&1 9>&- &
  pid=$!
  trap 'exit 130' INT
  trap 'exit 143' TERM
  trap 'status=$?; if [ "$status" -ne 0 ]; then stop_cluster || echo "Startup cleanup failed; run scripts/stop.sh and check all four nodes." >&2; fi' EXIT
  started="$(awk '{sub(/.*\) /, ""); print $20}' "/proc/$pid/stat")" || return 1
  printf '%s %s %s\n' "$pid" "$started" "$BOOT_ID" > "$PID_FILE"

  echo "Waiting for DeepSeek-V4.1-Flash; log: $LOG_FILE"
  deadline=$((SECONDS + 3000))
  while ((SECONDS < deadline)); do
    if ! launcher_running; then
      echo "Appliance startup failed. See $LOG_FILE." >&2
      tail -n 50 "$LOG_FILE" >&2
      return 1
    fi
    if [ "$(timeout --kill-after=1s 2s docker inspect --format '{{.State.Running}}' "$CONTAINER_NAME" 2>/dev/null)" = true ] &&
      curl --noproxy '*' -fsS --connect-timeout 1 --max-time 1 "http://127.0.0.1:$VLLM_PORT/health" >/dev/null 2>&1 && launcher_running; then
      trap - EXIT INT TERM
      echo "DeepSeek-V4.1-Flash is ready on port $VLLM_PORT."
      return 0
    fi
    sleep 5
  done
  echo "Timed out waiting for DeepSeek-V4.1-Flash. See $LOG_FILE." >&2
  return 1
}

case "${1:-}" in
  start) start_cluster ;;
  stop) stop_cluster ;;
  restart) stop_cluster && start_cluster ;;
  *) echo "Usage: $0 start|stop|restart" >&2; exit 2 ;;
esac
