#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"

export ENV_FILE="$APP_DIR/.env"
export COMPOSE_FILE="$APP_DIR/docker-compose.yml"
export PROJECT_NAME="${PROJECT_NAME:-vllm-gb10-dspark}"

exec bash "$APP_DIR/stop-deepseek-v4-flash-dspark.sh" "$@"
