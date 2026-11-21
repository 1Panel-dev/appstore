#!/bin/bash
set -euo pipefail

# Generate a preconfigured offline admin token on first install so the server
# never exposes the unauthenticated bootstrap window.
# Reference: https://docs.influxdata.com/influxdb3/core/admin/tokens/admin/preconfigured/

mkdir -p data plugins
chmod 700 data
chmod 750 plugins

token_file="data/admin-token.json"

if [ ! -s "$token_file" ]; then
    set +o pipefail
    token="apiv3_$(LC_ALL=C tr -dc 'A-Za-z0-9' </dev/urandom | head -c 32)"
    set -o pipefail

    tmp_file="$(mktemp data/.admin-token.XXXXXX)"
    printf '{"token":"%s","name":"_admin","description":"Admin token for InfluxDB 3 Core"}\n' "$token" >"$tmp_file"
    chmod 600 "$tmp_file"
    mv "$tmp_file" "$token_file"
fi

chmod 600 "$token_file"

# The image runs as influxdb3 (UID/GID 1500); make the mounted directories writable.
chown -R 1500:1500 data plugins
