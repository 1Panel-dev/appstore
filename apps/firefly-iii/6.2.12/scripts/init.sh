#!/bin/bash

ENV_FILE="./.env"

if [[ -f "$ENV_FILE" ]]; then
  if grep -q "^PANEL_DB_TYPE=['\"]mysql['\"]" "$ENV_FILE"; then
    echo "PANEL_DB_TYPE 为 Mysql 数据库, 不作修改"
  else
    sed -i -E "s/^PANEL_DB_TYPE=['\"]?postgres['\"]?/PANEL_DB_TYPE='pgsql'/" "$ENV_FILE"
    echo "已将 PANEL_DB_TYPE 修改为 pgsql"
  fi
else
  echo ".env 文件不存在"
fi