#!/bin/bash

# Generated from official installation evidence:
# - https://github.com/grokability/snipe-it/blob/v8.8.4/docker/startup.sh
# - https://snipe-it.readme.io/docs/docker

set -euo pipefail
umask 077

mkdir -p data
key_file=data/app-key
if [ -s "$key_file" ]; then
    exit 0
fi

temp_key=$(mktemp data/.app-key.XXXXXX)
trap 'rm -f "$temp_key"' EXIT
printf 'base64:' > "$temp_key"
head -c 32 /dev/urandom | base64 >> "$temp_key"
ln "$temp_key" "$key_file" || test -s "$key_file"
