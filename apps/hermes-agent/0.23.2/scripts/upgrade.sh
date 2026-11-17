#!/bin/bash

set -e

ENV_FILE="./.env"
USERNAME_KEY="HERMES_DASHBOARD_USERNAME"
PASSWORD_KEY="HERMES_DASHBOARD_PASSWORD"

random_password() {
  local password
  while true; do
    password=$(LC_ALL=C tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 8)
    if [[ ${#password} -eq 8 && "$password" =~ [A-Z] && "$password" =~ [a-z] && "$password" =~ [0-9] ]]; then
      echo "$password"
      return
    fi
  done
}

append_if_missing() {
  local key="$1"
  local value="$2"
  if [ ! -f "$ENV_FILE" ]; then
    touch "$ENV_FILE"
  fi
  if grep -q "^${key}=" "$ENV_FILE"; then
    echo "${key} already exists"
    return
  fi
  echo "${key}=\"${value}\"" >> "$ENV_FILE"
  echo "Added ${key}"
}

append_if_missing "$USERNAME_KEY" "admin"
append_if_missing "$PASSWORD_KEY" "$(random_password)"
