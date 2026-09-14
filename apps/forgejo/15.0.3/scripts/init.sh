#!/bin/bash

ENV_FILE="./.env"

[ -f "${ENV_FILE}" ] && source "${ENV_FILE}"

if [[ -f "${ENV_FILE}" ]] && grep -Eq "^PANEL_DB_TYPE=['\"]?mariadb['\"]?$" "${ENV_FILE}"; then
    sed -i.bak -E "s/^PANEL_DB_TYPE=['\"]?mariadb['\"]?$/PANEL_DB_TYPE='mysql'/" "${ENV_FILE}"
    rm -f "${ENV_FILE}.bak"
fi

# renovate: datasource=docker depName=forgejo/forgejo
IMAGE=codeberg.org/forgejo/forgejo:15.0.3

if [ "${ROOTLESS}" = "true" ]; then
    IMAGE="${IMAGE}-rootless"
    mkdir -p ./data-rootless/forgejo ./data-rootless/conf
    chown -R 1000:1000 ./data-rootless/forgejo ./data-rootless/conf
fi
echo "IMAGE=${IMAGE}" >> .env
