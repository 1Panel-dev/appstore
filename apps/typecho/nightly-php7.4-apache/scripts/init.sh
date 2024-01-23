#!/bin/bash

if [ -f ./.env ]; then
    if grep -q "PANEL_DB_TYPE" ./.env; then
        echo "PANEL_DB_TYPE 已存在"
    else
        echo 'PANEL_DB_TYPE="mysql"' >> ./.env
    fi
    
    if grep -q "TYPECHO_DB_ADAPTER" ./.env; then
        echo "TYPECHO_DB_ADAPTER 已存在"
    else
      PANEL_DB_TYPE_VALUE=$(grep -E '^PANEL_DB_TYPE=' ./.env | cut -d '=' -f 2)
      
      if [[ "$PANEL_DB_TYPE_VALUE" =~ mysql ]]; then
          echo 'TYPECHO_DB_ADAPTER="Pdo_Mysql"' >> ./.env
      elif [[ "$PANEL_DB_TYPE_VALUE" =~ mariadb ]]; then
          echo 'TYPECHO_DB_ADAPTER="Pdo_Mysql"' >> ./.env
      elif [[ "$PANEL_DB_TYPE_VALUE" =~ postgresql ]]; then
          echo 'TYPECHO_DB_ADAPTER="Pdo_Pgsql"' >> ./.env
      fi
    fi
else
    echo ".env 文件不存在"
fi