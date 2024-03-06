#!/bin/bash

if [ -f .env ]; then
  source .env

  mkdir -p "$MINIO_ROOT_PATH"

  mkdir -p "$MINIO_ROOT_PATH/data"
  mkdir -p "$MINIO_ROOT_PATH/certs"

  echo "Directories set successfully."

else
  echo "Error: .env file not found."
  exit 1
fi
