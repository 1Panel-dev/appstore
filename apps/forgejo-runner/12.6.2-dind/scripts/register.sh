#!/bin/sh
set -e

# 等待 dockerd 可用
echo "⏳ Waiting for Docker daemon on dind..."
until curl -s http://dind:2375/_ping | grep -q "OK"; do
  sleep 1
done
echo "✅ Docker daemon is ready."

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
