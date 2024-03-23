#!/bin/bash

if [[ -f ./.env ]]; then
  source .env

  echo "Check Finish."

else
  echo ".env not found."
fi
