#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
source "$APP_DIR/scripts/config.sh"

test "$(uname -s)" = Linux
for command in docker ssh python3 curl nohup setsid flock timeout awk; do
  command -v "$command" >/dev/null || {
    echo "Missing required command: $command" >&2
    exit 1
  }
done

test -d "$DSPARK_MODEL_HOST" || {
  echo "Missing model directory on head: $DSPARK_MODEL_HOST" >&2
  exit 1
}
docker image inspect "$DSPARK_VLLM_IMAGE" >/dev/null || {
  echo "Missing local application image: $DSPARK_VLLM_IMAGE" >&2
  exit 1
}
chmod 0600 "$ENV_FILE"
chmod 0755 "$APP_DIR/launch-cluster.sh" "$APP_DIR/appliance/"*.sh "$APP_DIR/scripts/"*.sh
