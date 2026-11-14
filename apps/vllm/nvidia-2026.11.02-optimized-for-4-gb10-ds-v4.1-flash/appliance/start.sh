#!/bin/bash
# Start the DeepSeek-V4.1-Flash appliance from the head Spark.
#
# Run this on the head node only. It starts the container on all nodes listed
# in .env and launches vLLM on every node (head rank 0, workers headless).
# The process stays in the foreground and tails the head container log.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

source "$ROOT/scripts/config.sh"
LAUNCH_SCRIPT="$ROOT/appliance/deepseek-v4.1-flash.sh"

die() { echo "Error: $*" >&2; exit 1; }

[[ -d "$DSPARK_MODEL_HOST" ]] || die "Model directory not found: $DSPARK_MODEL_HOST"
[[ -f "$LAUNCH_SCRIPT" ]] || die "Launch script not found: $LAUNCH_SCRIPT"
command -v docker >/dev/null 2>&1 || die "docker is not installed."
docker image inspect "$DSPARK_VLLM_IMAGE" >/dev/null 2>&1 || die "Docker image '$DSPARK_VLLM_IMAGE' not found locally. Load it with: docker load -i <image.tar>"

# launch-cluster.sh reads CONTAINER_NAME from .env; use the same value for the
# already-running guard below.
if docker ps --format '{{.Names}}' | grep -qx "$CONTAINER_NAME"; then
    die "Container '$CONTAINER_NAME' is already running on this node. Run appliance/stop.sh first."
fi

echo "Starting DeepSeek-V4.1-Flash appliance"
echo "  image:  $DSPARK_VLLM_IMAGE"
echo "  model:  $DSPARK_MODEL_HOST -> $DSPARK_MODEL_CONTAINER (read-only)"
echo "  config: $ROOT/.env"
echo

exec "$ROOT/launch-cluster.sh" \
    -t "$DSPARK_VLLM_IMAGE" \
    -v "$DSPARK_MODEL_HOST:$DSPARK_MODEL_CONTAINER:ro" \
    --name "$CONTAINER_NAME" \
    -e "DSPARK_MODEL_CONTAINER=$DSPARK_MODEL_CONTAINER" \
    -e "SERVED_MODEL_NAME=$SERVED_MODEL_NAME" \
    -e "VLLM_PORT=$VLLM_PORT" \
    -e "VLLM_API_KEY=$VLLM_API_KEY" \
    --launch-script "$LAUNCH_SCRIPT"
