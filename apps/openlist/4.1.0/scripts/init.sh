#!/bin/bash

[ -f ./.env ] && source ./.env

# Default image name without pre-install environment
OPENLIST_IMAGE=openlistteam/openlist:v4.1.0

{
  # Retain the original environment variables
  grep -vE '^OPENLIST_IMAGE' ./.env 2>/dev/null

  if [ -n "$PRE_INSTALLED" ]; then
    # Change images with pre-install environment
    OPENLIST_IMAGE=$OPENLIST_IMAGE-$PRE_INSTALLED
  fi
  echo "OPENLIST_IMAGE=${OPENLIST_IMAGE}"
  echo ""
} > ./.env.tmp && mv ./.env.tmp ./.env
