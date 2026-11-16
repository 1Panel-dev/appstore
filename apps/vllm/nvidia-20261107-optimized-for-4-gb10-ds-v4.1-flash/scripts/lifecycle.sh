#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
export ENV_FILE="$APP_DIR/.env"
test -f "$ENV_FILE" || {
  echo "Missing application environment file: $ENV_FILE" >&2
  exit 1
}
set -a
source "$ENV_FILE"
set +a

for name in DSPARK_VLLM_IMAGE DSPARK_MODEL_HOST DSPARK_MODEL_CONTAINER VLLM_PORT SERVED_MODEL_NAME CONTAINER_NAME CLUSTER_NODES LOCAL_IP ETH_IF IB_IF GID_INDEX DRI_CARD CACHE_ROOT MASTER_PORT; do
  if [ -z "${!name:-}" ]; then
    echo "Missing required configuration: $name" >&2
    exit 1
  fi
done
export VLLM_API_KEY="${VLLM_API_KEY:-}"
if [[ ! "$VLLM_PORT" =~ ^[1-9][0-9]{0,4}$ ]] || ((VLLM_PORT > 65535)); then
  echo "VLLM_PORT must be an integer between 1 and 65535." >&2
  exit 1
fi
for name in DSPARK_MODEL_HOST DSPARK_MODEL_CONTAINER CACHE_ROOT; do
  if [[ ! "${!name}" =~ ^/[a-zA-Z0-9_./-]+$ ]]; then
    echo "$name must be an absolute path without spaces or shell special characters." >&2
    exit 1
  fi
done
if [[ ! "$DRI_CARD" =~ ^/[a-zA-Z0-9_.:/-]+$ ]]; then
  echo "DRI_CARD must be an absolute device path without spaces or shell special characters." >&2
  exit 1
fi
if [[ ! "$CONTAINER_NAME" =~ ^[a-zA-Z0-9][a-zA-Z0-9_.-]*$ ]]; then
  echo "Invalid CONTAINER_NAME." >&2
  exit 1
fi
if [[ ! "$DSPARK_VLLM_IMAGE" =~ ^[a-zA-Z0-9][a-zA-Z0-9_./:@-]*$ ]]; then
  echo "Invalid DSPARK_VLLM_IMAGE." >&2
  exit 1
fi
if [[ "$VLLM_API_KEY" == *$'\n'* || "$VLLM_API_KEY" == *$'\r'* ]]; then
  echo "VLLM_API_KEY must not contain line breaks." >&2
  exit 1
fi

if [[ ! "$MASTER_PORT" =~ ^[1-9][0-9]{0,4}$ ]] || ((MASTER_PORT > 65535)) || [[ "$MASTER_PORT" == "$VLLM_PORT" ]]; then
  echo "MASTER_PORT must be a valid port different from VLLM_PORT." >&2
  exit 1
fi
if [[ ! "$ETH_IF" =~ ^[a-zA-Z0-9_.-]+$ || ! "$IB_IF" =~ ^[a-zA-Z0-9_.-]+,[a-zA-Z0-9_.-]+$ || ! "$GID_INDEX" =~ ^[0-9]+$ ]]; then
  echo "Configure ETH_IF, two comma-separated RoCE devices in IB_IF, and a numeric GID_INDEX." >&2
  exit 1
fi

case "${1:-}" in
  init)
    test "$(uname -s)" = Linux
    for command in docker ssh python3 flock; do
      command -v "$command" >/dev/null || {
        echo "Missing required command: $command" >&2
        exit 1
      }
    done
    docker compose version
    python3 -c 'import sys; sys.exit(0 if sys.version_info >= (3, 10) else "Python 3.10 or newer is required")'

    test -d "$DSPARK_MODEL_HOST" || {
      echo "Missing model directory on head: $DSPARK_MODEL_HOST" >&2
      exit 1
    }
    chmod 0600 "$ENV_FILE"
    chmod 0755 "$APP_DIR/scripts/"*.sh
    exit 0
    ;;
  start|stop|restart|uninstall) ;;
  *) echo "Usage: $0 init|start|stop|restart|uninstall" >&2; exit 2 ;;
esac

mkdir -p "$APP_DIR/.runtime"
chmod 0700 "$APP_DIR/.runtime"
exec 9>"$APP_DIR/.runtime/lifecycle.lock"
flock -n 9 || { echo "Another lifecycle operation is running." >&2; exit 1; }
exec python3 "$APP_DIR/scripts/cluster.py" "$1"
