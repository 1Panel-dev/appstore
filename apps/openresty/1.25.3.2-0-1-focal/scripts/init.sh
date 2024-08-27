#!/bin/bash

source ./.env

sed -i -E "s/(listen[[:space:]]+)(80)([[:space:]]*;)/\1${PANEL_APP_PORT_HTTP}\3/" conf/conf.d/00.default.conf
sed -i -E "s/(listen[[:space:]]+)(443)([[:space:]]+ssl[[:space:]]+http2;)/\1${PANEL_APP_PORT_HTTPS}\3/" conf/conf.d/00.default.conf

sed -i -E "s/(listen[[:space:]]+)(80)([[:space:]]*;)/\1${PANEL_APP_PORT_HTTP}\3/" conf/conf.d/default.conf

