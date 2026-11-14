#!/bin/bash
# Start the DeepSeek-V4.1-Flash appliance from the head Spark.
#
# Run this on the head node only. It starts the container on all nodes listed
# in .env and launches vLLM on every node (head rank 0, workers headless).
# The process stays in the foreground and tails the head container log.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

IMAGE="${IMAGE_NAME:-eugr/spark-vllm-b12x:latest}"
MODEL_HOST_DIR="${DSPARK_MODEL_HOST:-/opt/deepseek-ai/DeepSeek-V4.1-Flash}"
MODEL_CONTAINER_PATH="${MODEL_CONTAINER_PATH:-/models/DeepSeek-V4.1-Flash}"
LAUNCH_SCRIPT="$ROOT/appliance/deepseek-v4.1-flash.sh"

die() { echo "Error: $*" >&2; exit 1; }

[[ -f "$ROOT/.env" ]] || die "$ROOT/.env is missing. Copy .env.template to .env and fill it in (or run setup-cluster.sh)."
grep -q '^CLUSTER_NODES=' "$ROOT/.env" || die "$ROOT/.env has no CLUSTER_NODES entry."
[[ -d "$MODEL_HOST_DIR" ]] || die "Model directory not found: $MODEL_HOST_DIR"
[[ -f "$LAUNCH_SCRIPT" ]] || die "Launch script not found: $LAUNCH_SCRIPT"
command -v docker >/dev/null 2>&1 || die "docker is not installed."
docker image inspect "$IMAGE" >/dev/null 2>&1 || die "Docker image '$IMAGE' not found locally. Load it with: docker load -i <image.tar>"

# launch-cluster.sh reads CONTAINER_NAME from .env; use the same value for the
# already-running guard below.
CONTAINER="$(sed -n 's/^CONTAINER_NAME=//p' "$ROOT/.env" | tail -n 1 | tr -d "\"'")"
CONTAINER="${CONTAINER:-vllm_node}"

if docker ps --format '{{.Names}}' | grep -qx "$CONTAINER"; then
    die "Container '$CONTAINER' is already running on this node. Run appliance/stop.sh first."
fi

echo "Starting DeepSeek-V4.1-Flash appliance"
echo "  image:  $IMAGE"
echo "  model:  $MODEL_HOST_DIR -> $MODEL_CONTAINER_PATH (read-only)"
echo "  config: $ROOT/.env"
echo

exec "$ROOT/launch-cluster.sh" \
    -t "$IMAGE" \
    -v "$MODEL_HOST_DIR:$MODEL_CONTAINER_PATH:ro" \
    --launch-script "$LAUNCH_SCRIPT"
