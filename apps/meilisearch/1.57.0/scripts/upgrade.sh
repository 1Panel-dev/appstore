#!/bin/bash

ENV_FILE="./.env"

if [[ -f $ENV_FILE ]]; then
  source "$ENV_FILE"
  if [ -z "$MEILI_NO_ANALYTICS" ] || [ "$MEILI_NO_ANALYTICS" = "" ]; then
    NEW_VALUE='"true"'
  elif [ "$MEILI_NO_ANALYTICS" = "--no-analytics" ]; then
    NEW_VALUE='"false"'
  else
    NEW_VALUE="\"$MEILI_NO_ANALYTICS\""
  fi
  sed -i "s/^MEILI_NO_ANALYTICS=.*/MEILI_NO_ANALYTICS=$NEW_VALUE/" "$ENV_FILE"
else
  echo ".env 文件不存在"
fi