#!/bin/bash

if [[ -f ./.env ]]; then
  if grep -q 'PANEL_APP_PORT_HTTP' ./.env; then
    echo "PANEL_APP_PORT_HTTP 参数已存在"
  else
    echo 'PANEL_APP_PORT_HTTP=8080' >> ./.env
    echo "已添加 PANEL_APP_PORT_HTTP=8080"
  fi
else
  echo ".env 文件不存在"
fi
