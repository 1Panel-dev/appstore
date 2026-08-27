#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
APP_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"

bash "$SCRIPT_DIR/stop.sh"

set -a
# shellcheck disable=SC1091
source "$APP_DIR/.env"
set +a

ssh -o BatchMode=yes -o ConnectTimeout=10 "$WORKER_HOST" \
  "rm -f '$WORKER_DIR/.env.dspark' '$WORKER_DIR/docker-compose.dspark.yml'"
