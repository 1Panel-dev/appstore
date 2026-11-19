#!/bin/bash

ENV_FILE="./.env"

if [[ -f "$ENV_FILE" ]]; then
  if grep -q "^PANEL_DB_TYPE=['\"]mariadb['\"]" "$ENV_FILE"; then
    sed -i -E "s/^PANEL_DB_TYPE=['\"]?mariadb['\"]?/PANEL_DB_TYPE='mysql'/" "$ENV_FILE"
    echo "已将 PANEL_DB_TYPE 修改为 mysql"
  fi
else
  echo ".env 文件不存在"
fi
