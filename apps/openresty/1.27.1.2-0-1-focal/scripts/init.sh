#!/bin/bash

source ./.env

sed -i -E "s/(listen[[:space:]]+)80([[:space:]]*default_server;)/\1${PANEL_APP_PORT_HTTP}\2/" conf/default/00.default.conf
sed -i -E "s/(listen[[:space:]]+)\[::]:80([[:space:]]*default_server;)/\1\[::]:${PANEL_APP_PORT_HTTP}\2/" conf/default/00.default.conf

sed -i -E "s/(listen[[:space:]]+)(80)([[:space:]]*;)/\1${PANEL_APP_PORT_HTTP}\3/" conf/default/default.conf