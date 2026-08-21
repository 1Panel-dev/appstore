#!/bin/bash

if [[ -f ./.env ]]; then
  if grep -q "DISCOURSE_SKIP_BOOTSTRAP" ./.env; then
    echo "DISCOURSE_SKIP_BOOTSTRAP 已存在"
  else
    echo 'DISCOURSE_SKIP_BOOTSTRAP="yes"' >> ./.env
  fi
else
  echo ".env 文件不存在"
fi