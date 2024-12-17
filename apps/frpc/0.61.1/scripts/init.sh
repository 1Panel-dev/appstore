#!/bin/bash

source ./.env

sed -i "s/serverAddr = \".*\"/serverAddr = \"${SERVER_ADDRESS}\"/" ./data/frpc.toml
sed -i "s/serverPort = .*$/serverPort = ${SERVER_PORT}/" ./data/frpc.toml
sed -i "s/auth\.token = \".*\"/auth.token = \"${AUTH_TOKEN}\"/" ./data/frpc.toml
sed -i "s/webServer\.port = .*$/webServer.port = ${PANEL_APP_PORT_HTTP}/" ./data/frpc.toml
sed -i "s/webServer\.user = \".*\"/webServer.user = \"${USER_NAME}\"/" ./data/frpc.toml
sed -i "s/webServer\.password = \".*\"/webServer.password = \"${PASSWORD}\"/" ./data/frpc.toml