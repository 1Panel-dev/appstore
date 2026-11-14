#!/bin/bash
# Stop the DeepSeek-V4.1-Flash appliance on every node listed in .env.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

IMAGE="${IMAGE_NAME:-eugr/spark-vllm-b12x:latest}"

die() { echo "Error: $*" >&2; exit 1; }

[[ -f "$ROOT/.env" ]] || die "$ROOT/.env is missing; cannot determine cluster nodes."
grep -q '^CLUSTER_NODES=' "$ROOT/.env" || die "$ROOT/.env has no CLUSTER_NODES entry."

exec "$ROOT/launch-cluster.sh" -t "$IMAGE" stop
