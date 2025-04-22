#!/bin/bash

if [[ -f ./.env ]]; then
  if grep -q 'PANEL_DB_TYPE="mysql"' ./.env; then
    echo "PANEL_DB_TYPE 为 Mysql 数据库, 不作修改"
  else
    sed -i 's/PANEL_DB_TYPE="postgres"/PANEL_DB_TYPE="pgsql"/g' ./.env
  fi
else
  echo ".env 文件不存在"
fi