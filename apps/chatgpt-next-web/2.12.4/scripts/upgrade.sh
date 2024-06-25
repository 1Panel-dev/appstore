#!/bin/bash

if [[ -f ./.env ]]; then
  if grep -q "WHITE_WEBDEV_ENDPOINTS" ./.env; then
    echo "WHITE_WEBDEV_ENDPOINTS 已存在"
  else
    echo 'WHITE_WEBDEV_ENDPOINTS=""' >> ./.env
  fi
else
  echo ".env 文件不存在"
fi