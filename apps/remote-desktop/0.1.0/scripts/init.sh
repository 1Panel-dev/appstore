#!/usr/bin/env bash
# 1Panel pre-install: render coturn config and seed the download page into ./data/www.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
mkdir -p "$ROOT/data/coturn" "$ROOT/data/postgres" "$ROOT/data/www" "$ROOT/data/updates"

PUBLIC_IP="${PUBLIC_IP:?PUBLIC_IP is required}"
TURN_SECRET="${TURN_SECRET:?TURN_SECRET is required}"
TURN_PORT="${PANEL_APP_PORT_TURN:-3478}"
RELAY_MIN=49160
RELAY_MAX=49200

if [[ ${#TURN_SECRET} -lt 32 ]]; then
  echo "TURN_SECRET must be at least 32 characters" >&2
  exit 1
fi

if [[ ! "$ACCOUNT_SECRET_KEY" =~ ^[0-9a-fA-F]{64}$ ]]; then
  echo "ACCOUNT_SECRET_KEY must be 64 hex chars (openssl rand -hex 32)" >&2
  exit 1
fi

sed \
  -e "s|__TURN_SECRET__|${TURN_SECRET}|g" \
  -e "s|__PUBLIC_IP__|${PUBLIC_IP}|g" \
  -e "s|__RELAY_MIN__|${RELAY_MIN}|g" \
  -e "s|__RELAY_MAX__|${RELAY_MAX}|g" \
  "$ROOT/coturn/turnserver.conf.template" >"$ROOT/data/coturn/turnserver.conf"

if [[ ! -f "$ROOT/data/www/index.html" ]]; then
  sed \
    -e "s|__PUBLIC_IP__|${PUBLIC_IP}|g" \
    -e "s|__SIGNAL_PORT__|${PANEL_APP_PORT_HTTP:-3000}|g" \
    -e "s|__TURN_PORT__|${TURN_PORT}|g" \
    "$ROOT/www/index.html" >"$ROOT/data/www/index.html"
fi

chmod 644 "$ROOT/data/coturn/turnserver.conf"
echo "[remote-desktop] coturn config and download page ready"
