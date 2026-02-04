#!/bin/bash

[ -f ./.env ] && source ./.env

# renovate: datasource=docker depName=forgejo/forgejo
IMAGE=codeberg.org/forgejo/forgejo:14.0.1

if [ "${ROOTLESS}" = "true" ]; then
    IMAGE="${IMAGE}-rootless"
    mkdir -p ./data-rootless/forgejo ./data-rootless/conf
    chown -R 1000:1000 ./data-rootless/forgejo ./data-rootless/conf
fi
echo "IMAGE=${IMAGE}" >> .env