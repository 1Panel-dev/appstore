#!/bin/sh

if command -v supervisord > /dev/null 2>&1; then
    supervisord -c /etc/supervisord.conf
fi
