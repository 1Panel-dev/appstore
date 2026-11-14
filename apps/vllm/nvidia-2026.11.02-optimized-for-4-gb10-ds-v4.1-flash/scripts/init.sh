#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
export ENV_FILE="$APP_DIR/.env"

test -f "$ENV_FILE" || {
  echo "Missing application environment file: $ENV_FILE" >&2
  exit 1
}
set -a
source "$ENV_FILE"
set +a
export IMAGE_NAME="${IMAGE_NAME:-eugr/spark-vllm-b12x:latest}"
MODEL_HOST_DIR="${DSPARK_MODEL_HOST:-/opt/deepseek-ai/DeepSeek-V4.1-Flash}"

test "$(uname -s)" = Linux
for command in docker ssh python3 curl nohup setsid flock timeout awk; do
  command -v "$command" >/dev/null || {
    echo "Missing required command: $command" >&2
    exit 1
  }
done

test -d "$MODEL_HOST_DIR" || {
  echo "Missing model directory on head: $MODEL_HOST_DIR" >&2
  exit 1
}
docker image inspect "$IMAGE_NAME" >/dev/null || {
  echo "Missing local application image: $IMAGE_NAME" >&2
  exit 1
}
chmod 0600 "$ENV_FILE"
chmod 0755 "$APP_DIR/launch-cluster.sh" "$APP_DIR/appliance/"*.sh "$APP_DIR/scripts/"*.sh
