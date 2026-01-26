#!/bin/sh
set -e

cd /data

if [ ! -s .runner ]; then
  echo ">>> Registering runner..."
  forgejo-runner register --no-interactive \
    --instance "$FORGEJO_INSTANCE_URL" \
    --token "$RUNNER_REGISTRATION_TOKEN" \
    --name "$RUNNER_NAME" \
    --labels "$RUNNER_LABELS"
  forgejo-runner generate-config > config.yml
fi

echo ">>> Starting daemon..."
exec forgejo-runner --config config.yml daemon
