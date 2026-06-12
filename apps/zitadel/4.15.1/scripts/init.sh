#!/bin/bash
# ZITADEL init
# - Generates the masterkey (must be exactly 32 characters). It encrypts all
#   keys/secrets of the instance: losing it makes the instance unrecoverable,
#   and it must never change once the instance is initialized.
# - Idempotent: an existing key is kept on upgrade/reinstall.
# - Why not the form "random" button: 1Panel random values are ~8 characters,
#   which fails ZITADEL's strict 32-character requirement at startup.
set -e

mkdir -p ./conf

KEY_FILE=./conf/.masterkey
if [ ! -s "$KEY_FILE" ]; then
  # openssl rand -hex 16 = 32 hex characters
  (openssl rand -hex 16 2>/dev/null || head -c 16 /dev/urandom | od -An -tx1 | tr -d ' \n') > "$KEY_FILE"
  chmod 600 "$KEY_FILE"
fi

cat > ./conf/.env << EOF
ZITADEL_MASTERKEY=$(cat "$KEY_FILE")
EOF
chmod 600 ./conf/.env

echo "[zitadel-init] ZITADEL_MASTERKEY ready (32 chars, persisted in ./conf/.masterkey — keep it safe)"
