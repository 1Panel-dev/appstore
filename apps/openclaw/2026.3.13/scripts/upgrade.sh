#!/bin/bash
set -e

ensure_https_port_env() {
  local env_file=".env"

  if [ ! -f "$env_file" ]; then
    echo ".env file not found, skip HTTPS port migration."
    return
  fi

  if grep -q '^PANEL_APP_PORT_HTTPS=' "$env_file"; then
    echo "PANEL_APP_PORT_HTTPS already exists."
    return
  fi

  local http_port
  http_port=$(grep '^PANEL_APP_PORT_HTTP=' "$env_file" | tail -n 1 | cut -d'=' -f2-)
  if [ -z "$http_port" ]; then
    echo "PANEL_APP_PORT_HTTP not found, skip HTTPS port migration."
    return
  fi

  echo "PANEL_APP_PORT_HTTPS=${http_port}" >> "$env_file"
  echo "Migrated PANEL_APP_PORT_HTTP -> PANEL_APP_PORT_HTTPS=${http_port}"
}

ensure_https_port_env

OPENCLAW_IMAGE=$(grep -E '^\s*image:' "docker-compose.yml" | awk '{print $2}')
CLI_IMAGE=$(grep -E '^\s*image:' "docker-compose-cli.yml" | awk '{print $2}')

echo "Detected OpenClaw image: $OPENCLAW_IMAGE"
echo "Current CLI image: $CLI_IMAGE"

if [ "$OPENCLAW_IMAGE" = "$CLI_IMAGE" ]; then
    echo "CLI image is already up-to-date. No changes made."
else
    sed -i -E "s|^\s*image:.*|  image: $OPENCLAW_IMAGE|" "docker-compose-cli.yml"
    echo "CLI image has been updated to: $OPENCLAW_IMAGE"
fi

ARCH=$(uname -m)

case "$ARCH" in
  x86_64|amd64)
    BIN="openclaw-setup-linux-amd64"
    ;;
  aarch64|arm64)
    BIN="openclaw-setup-linux-arm64"
    ;;
  *)
    echo "Unsupported architecture: $ARCH"
    exit 1
    ;;
esac

chmod +x "scripts/${BIN}"
./scripts/${BIN} update controlUi

chown -R 1000:1000 data
