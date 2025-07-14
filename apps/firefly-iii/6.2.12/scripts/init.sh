#!/bin/bash

ENV_FILE="./.env"
DOCKER_COMPOSE_FILE="./docker-compose.yml"

if [[ -f "$ENV_FILE" ]]; then
  if grep -q "^PANEL_DB_TYPE=['\"]mysql['\"]" "$ENV_FILE"; then
    echo "PANEL_DB_TYPE 为 Mysql 数据库, 不作修改"
  else
    sed -i -E "s/^PANEL_DB_TYPE=['\"]?postgres['\"]?/PANEL_DB_TYPE='pgsql'/" "$ENV_FILE"
    echo "已将 PANEL_DB_TYPE 修改为 pgsql"
  fi

  PANEL_APP_URL=$(grep "^PANEL_APP_URL=" "$ENV_FILE" | cut -d '=' -f2 | tr -d "'\"")
  if [[ -z "$PANEL_APP_URL" ]]; then
    echo "PANEL_APP_URL 为空，开始修改 docker-compose.yml"

    sed -i '/- APP_URL=\${PANEL_APP_URL}/d' "$DOCKER_COMPOSE_FILE"
    sed -i '/- TRUSTED_PROXIES=\*\*/d' "$DOCKER_COMPOSE_FILE"

    echo "已删除 docker-compose.yml 中的 APP_URL 和 TRUSTED_PROXIES 配置"
  else
    echo "PANEL_APP_URL 不为空，保持 docker-compose.yml 不变"
  fi
else
  echo ".env 文件不存在"
fi
