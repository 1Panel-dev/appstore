#!/bin/bash

if [[ -f ./.env ]]; then
  if grep -q 'WEBSITE_DIR' ./.env; then
    echo "WEBSITE_DIR 参数已存在"
  else
    echo 'WEBSITE_DIR="./www"' >> ./.env
    echo "已添加 WEBSITE_DIR=./www"
  fi
else
  echo ".env 文件不存在"
fi
