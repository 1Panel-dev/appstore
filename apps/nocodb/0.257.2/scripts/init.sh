#!/bin/bash

ENV_FILE=".env"

if [ -f "$ENV_FILE" ]; then
    PANEL_DB_TYPE=$(grep '^PANEL_DB_TYPE=' "$ENV_FILE" | cut -d '=' -f 2 | tr -d '"')
    
    if [ "$PANEL_DB_TYPE" == "postgresql" ]; then
        NEW_DB_TYPE="pg"
    elif [ "$PANEL_DB_TYPE" == "mysql" ] || [ "$PANEL_DB_TYPE" == "mariadb" ]; then
        NEW_DB_TYPE="mysql2"
    else
        echo "Unsupported PANEL_DB_TYPE value: $PANEL_DB_TYPE"
        exit 1
    fi

    sed -i "s/^PANEL_DB_TYPE=.*/PANEL_DB_TYPE=\"$NEW_DB_TYPE\"/" "$ENV_FILE"

else
    echo ".env file not found!"
    exit 1
fi