#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

bash "$SCRIPT_DIR/stop.sh"
exec bash "$SCRIPT_DIR/start.sh" "$@"
