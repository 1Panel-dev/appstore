#!/bin/bash

set -euo pipefail

BASE_DIR=$(cd "$(dirname "$0")/.." && pwd)

"${BASE_DIR}/scripts/render-config.sh"

chown -R 999:999 "${BASE_DIR}/data/postgres" || true
