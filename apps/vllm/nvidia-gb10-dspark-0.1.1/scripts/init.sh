#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"

install -d -m 0755 /opt/1pds/cache
chmod 0600 "$APP_DIR/.env"
chmod 0755 "$APP_DIR/scripts"/*.sh

set -a
# shellcheck disable=SC1091
source "$APP_DIR/.env"
set +a

: "${DSPARK_MODEL_HOST:?DSPARK_MODEL_HOST must be set in $APP_DIR/.env}"

test -d "$DSPARK_MODEL_HOST" || {
  echo "Missing model directory on head: $DSPARK_MODEL_HOST" >&2
  exit 1
}

test -f "$DSPARK_MODEL_HOST/encoding/encoding_dsv4.py" || {
  echo "Missing encoding file on head: $DSPARK_MODEL_HOST/encoding/encoding_dsv4.py" >&2
  exit 1
}

ssh -o BatchMode=yes -o ConnectTimeout=10 "$WORKER_HOST" \
  "test -d '$DSPARK_MODEL_HOST' && test -f '$DSPARK_MODEL_HOST/encoding/encoding_dsv4.py'" || {
  echo "Missing model directory or encoding file on worker $WORKER_HOST: $DSPARK_MODEL_HOST" >&2
  exit 1
}

ENV_FILE="$APP_DIR/.env" \
COMPOSE_FILE="$APP_DIR/docker-compose.yml" \
  bash "$APP_DIR/scripts/validate-dspark-config.sh"
