#!/bin/bash

source ./.env

sed -i "s/bindPort = .*$/bindPort = ${PANEL_APP_PORT_SERVICE}/" ./data/frps.toml
sed -i "s/auth\.token = \".*\"/auth.token = \"${AUTH_TOKEN}\"/" ./data/frps.toml
sed -i "s/webServer\.port = .*$/webServer.port = ${PANEL_APP_PORT_HTTP}/" ./data/frps.toml
sed -i "s/webServer\.user = \".*\"/webServer.user = \"${USER_NAME}\"/" ./data/frps.toml
sed -i "s/webServer\.password = \".*\"/webServer.password = \"${PASSWORD}\"/" ./data/frps.toml
