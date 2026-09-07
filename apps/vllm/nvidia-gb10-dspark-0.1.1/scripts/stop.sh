#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"

export ENV_FILE="$APP_DIR/.env"
export COMPOSE_FILE="$APP_DIR/docker-compose.yml"

set -a
# shellcheck disable=SC1091
source "$ENV_FILE"
set +a

export PROJECT_NAME="${PROJECT_NAME:-vllm-gb10-dspark}"

case "${DSPARK_DEPLOY_PROFILE:-flash-0731}" in
  flash-0731|vision-exp) ;;
  *)
    echo "Unsupported DSPARK_DEPLOY_PROFILE: ${DSPARK_DEPLOY_PROFILE}" >&2
    exit 2
    ;;
esac

exec bash "$APP_DIR/scripts/stop-deepseek-v4-flash-dspark.sh" "$@"
