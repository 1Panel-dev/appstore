#!/bin/bash
# ZITADEL init
# - The masterkey encrypts all keys/secrets of the instance: it must be exactly
#   32 characters, must never change once the instance is initialized, and
#   losing it makes the instance unrecoverable.
# - If the user provides one in the form (optional), it is validated and used —
#   this also enables migrating an existing instance (fill in its old key).
# - If left empty, a strong random key is generated (the 1Panel form "random"
#   button only yields ~8 characters, which fails ZITADEL's 32-char check).
# - Idempotent: an existing key is kept; a *different* user-provided key is
#   rejected to protect an already-initialized instance.
set -e

mkdir -p ./conf
KEY_FILE=./conf/.masterkey

# Form values are written to ./.env by 1Panel before this script runs.
USER_KEY=""
if [ -f ./.env ]; then
  USER_KEY=$(grep -E '^ZITADEL_MASTER_KEY=' ./.env | head -1 | cut -d= -f2- | sed "s/^[\"']//;s/[\"']$//")
fi

if [ -n "$USER_KEY" ]; then
  if [ "${#USER_KEY}" -ne 32 ]; then
    echo "[zitadel-init] ERROR: masterkey must be exactly 32 characters (got ${#USER_KEY})" >&2
    exit 1
  fi
  if [ -s "$KEY_FILE" ] && [ "$(cat "$KEY_FILE")" != "$USER_KEY" ]; then
    echo "[zitadel-init] ERROR: a different masterkey already exists in ./conf/.masterkey." >&2
    echo "[zitadel-init] Changing the masterkey of an initialized instance makes its data unrecoverable." >&2
    echo "[zitadel-init] If you really mean it (e.g. fresh DB), delete ./conf/.masterkey and retry." >&2
    exit 1
  fi
  printf '%s' "$USER_KEY" > "$KEY_FILE"
elif [ ! -s "$KEY_FILE" ]; then
  # openssl rand -hex 16 = 32 hex characters
  (openssl rand -hex 16 2>/dev/null || head -c 16 /dev/urandom | od -An -tx1) | tr -d ' \n' > "$KEY_FILE"
fi
chmod 600 "$KEY_FILE"

cat > ./conf/.env << EOF
ZITADEL_MASTERKEY=$(cat "$KEY_FILE")
EOF
chmod 600 ./conf/.env

echo "[zitadel-init] ZITADEL_MASTERKEY ready (32 chars, persisted in ./conf/.masterkey — keep it safe)"
