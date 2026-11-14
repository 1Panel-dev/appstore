#!/bin/bash

ENV_FILE="./.env"

if [[ -f "$ENV_FILE" ]]; then
  if grep -q "PANEL_DB_TYPE" "$ENV_FILE"; then
    echo "PANEL_DB_TYPE 已存在"
  else
    echo 'PANEL_DB_TYPE="mysql"' >> "$ENV_FILE"
  fi
else
  echo ".env 文件不存在"
fi