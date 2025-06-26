#!/bin/bash

[ -f ./.env ] && source ./.env

# Default configuration
CASDOOR_DRIVER_NAME=${PANEL_DB_TYPE}
CASDOOR_DATASOURCE_NAME="${PANEL_DB_USER}:${PANEL_DB_USER_PASSWORD}@tcp(${PANEL_DB_HOST}:${PANEL_DB_PORT})/"

# Reset mariadb driver
if [ "$PANEL_DB_TYPE" = "mariadb" ]; then
  CASDOOR_DRIVER_NAME="mysql"
fi

# Reset postgresql datasource
if [ "$PANEL_DB_TYPE" = "postgres" ]; then
  CASDOOR_DATASOURCE_NAME="user=${PANEL_DB_USER} password=${PANEL_DB_USER_PASSWORD} host=${PANEL_DB_HOST} port=${PANEL_DB_PORT} dbname=${PANEL_DB_NAME} sslmode=disable"
fi

{
  # Retain the original environment variables
  grep -vE '^(CASDOOR_DRIVER_NAME|CASDOOR_DATASOURCE_NAME)' ./.env 2>/dev/null

  # Add CASDOOR_xx environment variables
  echo "CASDOOR_DRIVER_NAME=${CASDOOR_DRIVER_NAME}"
  echo "CASDOOR_DATASOURCE_NAME=\"${CASDOOR_DATASOURCE_NAME}\""
  echo ""
} > ./.env.tmp && mv ./.env.tmp ./.env

