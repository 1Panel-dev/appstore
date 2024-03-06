#!/bin/bash

if [[ -f ./.env ]]; then
  source .env
  mkdir -p "$MINIO_ROOT_PATH"
  mkdir -p "$MINIO_ROOT_PATH/data"
  mkdir -p "$MINIO_ROOT_PATH/certs"

  if grep -q "MINIO_ROOT_PATH" ./.env; then
    echo "MINIO_ROOT_PATH 已存在"
  else
    echo 'MINIO_ROOT_PATH="/home/minio"' >> ./.env
    echo 'Copy data to /home/minio'
    cp -r ./data /home/minio
  fi

  if grep -q "MINIO_BROWSER_SESSION_DURATION" ./.env; then
    echo "MINIO_BROWSER_SESSION_DURATION 已存在"
  else
    echo 'MINIO_BROWSER_SESSION_DURATION="12h"' >> ./.env
  fi

  if grep -q "MINIO_BROWSER" ./.env; then
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
