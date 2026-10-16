#!/bin/bash

[ -f ./.env ] && source ./.env

# Default image name without pre-install environment
# renovate: datasource=docker depName=openlistteam/openlist
IMAGE=openlistteam/openlist:v4.2.7

{
  # Retain the original environment variables
  grep -vE '^OPENLIST_IMAGE=' ./.env 2>/dev/null

  if [ -n "$PRE_INSTALLED" ]; then
    # Change images with pre-install environment
    IMAGE=$IMAGE-$PRE_INSTALLED
  fi
  echo "OPENLIST_IMAGE=${IMAGE}"
} > ./.env.tmp && mv ./.env.tmp ./.env
