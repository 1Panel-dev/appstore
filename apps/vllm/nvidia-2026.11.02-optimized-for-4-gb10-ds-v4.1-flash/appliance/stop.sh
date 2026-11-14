#!/bin/bash
# Stop the DeepSeek-V4.1-Flash appliance on every node listed in .env.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

source "$ROOT/scripts/config.sh"

exec "$ROOT/launch-cluster.sh" -t "$DSPARK_VLLM_IMAGE" --name "$CONTAINER_NAME" stop
