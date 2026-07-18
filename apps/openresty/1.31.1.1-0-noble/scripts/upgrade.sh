#!/bin/bash
NGINX_CONF="conf/nginx.conf"
MODULE_INCLUDE="include /usr/local/openresty/nginx/conf/modules-enabled/*.conf;"

mkdir -p modules conf/modules-enabled

if [ ! -f "$NGINX_CONF" ]; then
    echo "✗ failed:  $NGINX_CONF not found"
    exit 1
fi

if ! grep -Fq "$MODULE_INCLUDE" "$NGINX_CONF"; then
    sed -i "1i$MODULE_INCLUDE" "$NGINX_CONF"
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
