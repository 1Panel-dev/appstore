# #!/bin/bash

ENV_FILE=".env"

if [ -f "$ENV_FILE" ]; then
  source "$ENV_FILE"
  if [ "$PANEL_DB_TYPE" == "postgresql" ]; then
    ENGINE="postgresql"
  elif [ "$PANEL_DB_TYPE" == "mysql" ] || [ "$PANEL_DB_TYPE" == "mariadb" ]; then
    ENGINE="mysql"
  else
    echo "Unsupported PANEL_DB_TYPE value: $PANEL_DB_TYPE"
    exit 1
  fi

  DB_ENGINE_LINE=$(grep -E '^DB_ENGINE=' "$ENV_FILE")
  if [ -n "$DB_ENGINE_LINE" ]; then
    DB_ENGINE_VALUE=$(echo "$DB_ENGINE_LINE" | cut -d '=' -f2)
    if [ -z "$DB_ENGINE_VALUE" ]; then
      sed -i.bak "s/^DB_ENGINE=.*/DB_ENGINE=$ENGINE/" "$ENV_FILE"
    else
      echo "DB_ENGINE already set to '$DB_ENGINE_VALUE', skipping update."
    fi
  else
    echo "DB_ENGINE=$ENGINE" >> "$ENV_FILE"
  fi
else
    echo ".env file not found!"
    exit 1
fi
