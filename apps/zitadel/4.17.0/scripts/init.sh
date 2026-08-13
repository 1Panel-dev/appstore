#!/bin/bash
set -euo pipefail

# Generated from official installation evidence: https://zitadel.com/docs/self-hosting/manage/configure/configure#masterkey
# ZITADEL requires one immutable 32-byte master key. Generate it once in a
# package-relative file so 1Panel parameter edits and upgrades cannot replace it.

mkdir -p data
chmod 700 data
touch .env
chmod 600 .env

master_key_file="data/masterkey.env"
master_key=""

if [ -f "$master_key_file" ]; then
    master_key="$(sed -n 's/^ZITADEL_MASTERKEY=//p' "$master_key_file" | tail -n 1 | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'$//")"
fi

if [ -z "$master_key" ]; then
    set +o pipefail
    master_key="$(LC_ALL=C tr -dc A-Za-z0-9 </dev/urandom | head -c 32)"
    set -o pipefail
fi

if [ "${#master_key}" -ne 32 ]; then
    echo "ZITADEL master key must contain exactly 32 characters" >&2
    exit 1
fi

tmp_file="$(mktemp data/.masterkey.env.XXXXXX)"
printf 'ZITADEL_MASTERKEY=%s\n' "$master_key" >"$tmp_file"
chmod 600 "$tmp_file"
mv "$tmp_file" "$master_key_file"

admin_password="$(sed -n 's/^ZITADEL_FIRSTINSTANCE_ORG_HUMAN_PASSWORD=//p' .env | tail -n 1 | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'$//")"
if [ "${#admin_password}" -lt 8 ] \
    || [[ ! "$admin_password" =~ [A-Z] ]] \
    || [[ ! "$admin_password" =~ [a-z] ]] \
    || [[ ! "$admin_password" =~ [0-9] ]] \
    || [[ ! "$admin_password" =~ [^A-Za-z0-9] ]]; then
    echo "ZITADEL_FIRSTINSTANCE_ORG_HUMAN_PASSWORD must contain at least 8 characters, including uppercase and lowercase letters, a number, and a symbol" >&2
    exit 1
fi
