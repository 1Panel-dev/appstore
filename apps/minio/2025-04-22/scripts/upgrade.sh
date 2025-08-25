#!/bin/bash

if [[ -f ./.env ]]; then

  if grep -q "MINIO_BROWSER_SESSION_DURATION" ./.env; then
    echo "MINIO_BROWSER_SESSION_DURATION 已存在"
  else
    echo 'MINIO_BROWSER_SESSION_DURATION="12h"' >> ./.env
  fi

  if grep -q "MINIO_BROWSER=" ./.env; then
    echo "MINIO_BROWSER 已存在"
  else
    echo 'MINIO_BROWSER="on"' >> ./.env
  fi

  if grep -q "MINIO_BROWSER_LOGIN_ANIMATION" ./.env; then
    echo "MINIO_BROWSER_LOGIN_ANIMATION 已存在"
  else
    echo 'MINIO_BROWSER_LOGIN_ANIMATION="on"' >> ./.env
  fi
else
  echo ".env 文件不存在"
fi
