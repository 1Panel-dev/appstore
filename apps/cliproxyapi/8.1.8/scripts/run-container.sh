#!/bin/sh
set -eu

: "${MANAGEMENT_PASSWORD:?A management key is required}"

config_file=/data/config.yaml
if [ ! -e "$config_file" ]; then
    case "${CLIPROXYAPI_INITIAL_API_KEY:-}" in
        ''|*[!a-zA-Z0-9.%@!~_-]*)
            echo "The initial API key is missing or contains unsupported characters." >&2
            exit 1
            ;;
    esac

    umask 077
    temp_config=$(mktemp /data/config.yaml.XXXXXX)
    trap 'rm -f "$temp_config"' EXIT
    cat > "$temp_config" <<CONFIG
config-version: 8
server:
  port: 8317
access:
  api-keys:
    - '$CLIPROXYAPI_INITIAL_API_KEY'
oauth:
  auth-dir: /data/auths
plugins:
  enabled: false
  dir: /data/plugins
CONFIG
    if ! ln "$temp_config" "$config_file" && [ ! -f "$config_file" ]; then
        exit 1
    fi
    rm -f "$temp_config"
    trap - EXIT
fi

unset CLIPROXYAPI_INITIAL_API_KEY
exec /CLIProxyAPI/CLIProxyAPI -config "$config_file"
