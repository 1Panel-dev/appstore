#!/bin/bash

ENV_FILE=".env"

if [ -f "$ENV_FILE" ]; then
  PANEL_DB_TYPE=$(grep '^PANEL_DB_TYPE=' "$ENV_FILE" | cut -d '=' -f 2 | tr -d '"')
  
  if [ "$PANEL_DB_TYPE" == "postgresql" ]; then
    ENGINE="postgresql"
  elif [ "$PANEL_DB_TYPE" == "mysql" ] || [ "$PANEL_DB_TYPE" == "mariadb" ]; then
    ENGINE="mysql"
  else
    echo "Unsupported PANEL_DB_TYPE value: $PANEL_DB_TYPE"
    exit 1
  fi

  if grep -q '^DB_ENGINE=' "$ENV_FILE"; then
    sed -i.bak "s/^DB_ENGINE=.*/DB_ENGINE=$ENGINE/" "$ENV_FILE"
  else
    echo DB_ENGINE="$ENGINE" >> "$ENV_FILE"
  fi
else
    echo ".env file not found!"
    exit 1
fi