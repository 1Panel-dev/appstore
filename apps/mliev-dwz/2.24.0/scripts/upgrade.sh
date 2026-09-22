# #!/bin/bash

if [[ -f ./.env ]]; then
  if grep -q "^REDIS_HOST=" ./.env; then
    REDIS_VALUE=$(grep "^REDIS_HOST=" ./.env | cut -d'=' -f2-)
    sed -i '/^REDIS_HOST=/d' ./.env
    echo "PANEL_REDIS_HOST=${REDIS_VALUE}" >> ./.env
  fi
fi