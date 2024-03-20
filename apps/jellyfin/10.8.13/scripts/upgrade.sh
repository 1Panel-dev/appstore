#!/bin/bash

if [[ -f ./.env ]]; then
  source .env

  if grep -q "PANEL_APP_PORT_HTTPS" ./.env; then
    echo "PANEL_APP_PORT_HTTPS 已存在"
  else
    echo 'PANEL_APP_PORT_HTTPS=8920' >> ./.env
  fi

  if grep -q "NETWORK_MODE" ./.env; then
    echo "NETWORK_MODE 已存在"
  else
    echo 'NETWORK_MODE="1panel-network"' >> ./.env
  fi

  echo "Check Finish."

else
  echo ".env not found."
fi
