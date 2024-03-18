#!/bin/bash

if [[ -f ./.env ]]; then
  source .env

  mkdir -p "$EMBY_ROOT_PATH"

  mkdir -p "$EMBY_ROOT_PATH/data"
  mkdir -p "$EMBY_ROOT_PATH/mnt"

  chmod -R $UID:$GID "$EMBY_ROOT_PATH"

  echo "Check Finish."

else
  echo ".env not found."
fi
