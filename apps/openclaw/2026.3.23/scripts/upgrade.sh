#!/bin/bash

ENV_FILE="./.env"

if [[ ! -f "$ENV_FILE" ]]; then
  exit 0
fi

HTTPS_PORT=$(grep '^PANEL_APP_PORT_HTTPS=' "$ENV_FILE" | tail -n 1 | cut -d'=' -f2-)
if [[ -z "$HTTPS_PORT" ]]; then
  exit 0
fi

if grep -q '^PANEL_APP_PORT_HTTP=' "$ENV_FILE"; then
  sed -i.bak "s/^PANEL_APP_PORT_HTTP=.*/PANEL_APP_PORT_HTTP=${HTTPS_PORT}/" "$ENV_FILE"
  rm -f "$ENV_FILE.bak"
else
  echo "PANEL_APP_PORT_HTTP=${HTTPS_PORT}" >> "$ENV_FILE"
fi
