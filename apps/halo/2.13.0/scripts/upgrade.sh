#!/bin/bash

if [[ -f ./.env ]]; then
  if grep -q "HALO_CACHE_PAGE_DISABLED" ./.env; then
    echo "HALO_CACHE_PAGE_DISABLED 已存在"
  else
    echo 'HALO_CACHE_PAGE_DISABLED="false"' >> ./.env
  fi
else
  echo ".env 文件不存在"
fi
