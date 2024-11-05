#!/bin/bash

if [[ -f ./.env ]]; then
  if grep -q 'PANEL_APP_PORT_S3' ./.env; then
    echo "PANEL_APP_PORT_S3 参数已存在"
  else
    echo 'PANEL_APP_PORT_S3=5426' >> ./.env
    echo "已添加 PANEL_APP_PORT_S3=5426"
  fi
else
  echo ".env 文件不存在"
fi
