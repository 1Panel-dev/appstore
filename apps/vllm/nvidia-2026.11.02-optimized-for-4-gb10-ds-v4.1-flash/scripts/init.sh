#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
source "$APP_DIR/scripts/config.sh"

test "$(uname -s)" = Linux
for command in docker ssh scp python3 curl flock timeout; do
  command -v "$command" >/dev/null || {
    echo "Missing required command: $command" >&2
    exit 1
  }
done

test -d "$DSPARK_MODEL_HOST" || {
  echo "Missing model directory on head: $DSPARK_MODEL_HOST" >&2
  exit 1
}
docker compose version >/dev/null
python3 "$APP_DIR/scripts/compose-env.py" "$APP_DIR" "$APP_DIR/.runtime/0" 0 "${LOCAL_IP:?LOCAL_IP must be set in .env}"
install -m 0600 "$APP_DIR/.runtime/0/container.env" "$APP_DIR/container.env"
chmod 0600 "$ENV_FILE"
chmod 0755 "$APP_DIR/scripts/"*.sh
