#!/bin/sh

if command -v supervisord > /dev/null 2>&1; then
    php-fpm -y /usr/local/etc/php-fpm.conf &
    supervisord -c /etc/supervisord.conf
else
   php-fpm -y /usr/local/etc/php-fpm.conf
fi
