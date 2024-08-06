#!/bin/bash

if [ -f .env ]; then
  source .env

  echo "Check Finish."

else
  echo "Error: .env file not found."
fi
