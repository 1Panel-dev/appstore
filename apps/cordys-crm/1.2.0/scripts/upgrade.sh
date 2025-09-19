#!/bin/bash

if [[ -f ./.env ]]; then
  if grep -q "PANEL_APP_PORT_MCP" ./.env; then
    echo "PANEL_APP_PORT_MCP 已存在"
  else
    echo "PANEL_APP_PORT_MCP=8082" >> ./.env
  fi
else
  echo ".env 文件不存在"
fi
