#!/bin/bash

ENV_FILE="./.env"

if [[ -f "$ENV_FILE" ]]; then
  if grep -q "^PANEL_DB_TYPE=['\"]postgresql['\"]" "$ENV_FILE"; then
    sed -i -E "s/^PANEL_DB_TYPE=['\"]?postgresql['\"]?/PANEL_DB_TYPE='pgsql'/" "$ENV_FILE"
    echo "已将 PANEL_DB_TYPE 修改为 pgsql"
  fi
fi
