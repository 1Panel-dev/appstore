#!/bin/bash
set -e

OPENCLAW_IMAGE=$(grep -E '^\s*image:' "./docker-compose.yml" | awk '{print $2}')
CLI_IMAGE=$(grep -E '^\s*image:' "./docker-compose-cli.yml" | awk '{print $2}')

echo "Detected OpenClaw image: $OPENCLAW_IMAGE"
echo "Current CLI image: $CLI_IMAGE"

if [ "$OPENCLAW_IMAGE" = "$CLI_IMAGE" ]; then
    echo "CLI image is already up-to-date. No changes made."
else
    sed -i -E "s|^\s*image:.*|  image: $OPENCLAW_IMAGE|" "./docker-compose-cli.yml"
    echo "CLI image has been updated to: $OPENCLAW_IMAGE"
fi
