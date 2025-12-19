#!/bin/bash
NGINX_CONF="conf/nginx.conf"

STREAM_BLOCK='stream {
    log_format streamlog '\''$remote_addr[$time_local] '\''
                         '\''$protocol $status $bytes_sent $bytes_received '\''
                         '\''$session_time'\'';
    access_log /var/log/nginx/stream-access.log streamlog;
    access_log /dev/stdout streamlog;
                         
    include /usr/local/openresty/nginx/conf/stream.d/*.conf;
}'

if [ ! -f "$NGINX_CONF" ]; then
    echo "✗ failed:  $NGINX_CONF not found"
    exit 1
fi

if grep -q "stream[[:space:]]*{" "$NGINX_CONF"; then
    exit 0
fi

echo "$STREAM_BLOCK" >> "$NGINX_CONF"
