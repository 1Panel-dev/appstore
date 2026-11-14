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

for name in DSPARK_VLLM_IMAGE DSPARK_MODEL_HOST DSPARK_MODEL_CONTAINER VLLM_PORT SERVED_MODEL_NAME CONTAINER_NAME CLUSTER_NODES LOCAL_IP; do
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
for name in DSPARK_MODEL_HOST DSPARK_MODEL_CONTAINER; do
  if [[ ! "${!name}" =~ ^/[a-zA-Z0-9_./-]+$ ]]; then
    echo "$name must be an absolute path without spaces or shell special characters." >&2
    exit 1
  fi
done
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
