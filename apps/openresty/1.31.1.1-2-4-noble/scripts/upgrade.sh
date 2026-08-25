#!/bin/bash
NGINX_CONF="conf/nginx.conf"
MODULE_INCLUDE="include /usr/local/openresty/nginx/conf/modules-enabled/*.conf;"
HTTP_INCLUDE="include /usr/local/openresty/nginx/conf/http.d/*.conf;"
SITE_INCLUDE="include /usr/local/openresty/nginx/conf/conf.d/*.conf;"

mkdir -p modules conf/modules-enabled conf/http.d

if [ ! -f "$NGINX_CONF" ]; then
    echo "✗ failed:  $NGINX_CONF not found"
    exit 1
fi

if ! grep -Fq "$MODULE_INCLUDE" "$NGINX_CONF"; then
    sed -i "1i$MODULE_INCLUDE" "$NGINX_CONF"
fi

# http.d holds panel-managed http-context directives (compression, module
# runtime settings). It must be included before conf.d so that per-site
# configuration keeps overriding the global defaults.
if ! grep -Fq "$HTTP_INCLUDE" "$NGINX_CONF"; then
    if grep -Fq "$SITE_INCLUDE" "$NGINX_CONF"; then
        awk -v site="$SITE_INCLUDE" -v http="$HTTP_INCLUDE" '
            !done && index($0, site) {
                match($0, /^[ \t]*/)
                printf "%s%s\n", substr($0, 1, RLENGTH), http
                done = 1
            }
            { print }
        ' "$NGINX_CONF" > "$NGINX_CONF.tmp" && mv "$NGINX_CONF.tmp" "$NGINX_CONF"
    else
        echo "! skipped: conf.d include not found, add '$HTTP_INCLUDE' to the http block manually"
    fi
fi

STREAM_BLOCK='stream {
    log_format streamlog '\''$remote_addr[$time_local] '\''
                         '\''$protocol $status $bytes_sent $bytes_received '\''
                         '\''$session_time'\'';
    access_log /var/log/nginx/stream-access.log streamlog;
    access_log /dev/stdout streamlog;
                         
    include /usr/local/openresty/nginx/conf/stream.d/*.conf;
}'

if grep -q "stream[[:space:]]*{" "$NGINX_CONF"; then
    exit 0
fi

echo >> "$NGINX_CONF"
echo >> "$NGINX_CONF"
echo "$STREAM_BLOCK" >> "$NGINX_CONF"