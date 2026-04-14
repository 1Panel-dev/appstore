#!/bin/bash

set -euo pipefail

BASE_DIR=$(cd "$(dirname "$0")/.." && pwd)

mkdir -p \
    "${BASE_DIR}/data" \
    "${BASE_DIR}/data/postgres" \
    "${BASE_DIR}/data/containerd" \
    "${BASE_DIR}/data/cni" \
    "${BASE_DIR}/data/memoh" \
    "${BASE_DIR}/data/qdrant"

if [ ! -f "${BASE_DIR}/data/config.toml" ]; then
    "${BASE_DIR}/scripts/render-config.sh"
fi

chown -R 999:999 "${BASE_DIR}/data/postgres" || true
