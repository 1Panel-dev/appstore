#!/bin/sh
set -e
echo "Waiting for Docker daemon to be ready..."
apk update && apk add --no-cache curl
until curl -s http://dind:2375/_ping | grep -q "OK"; do
  sleep 1
done
echo "Docker daemon is ready."
cd /data
if [ -z "$RUNNER_LABELS" ]; then
  RUNNER_LABELS="docker"
fi
if [ ! -s .runner ]; then
  echo ">>> Registering runner..."
  forgejo-runner register --no-interactive \
    --instance "$FORGEJO_INSTANCE_URL" \
    --token "$RUNNER_REGISTRATION_TOKEN" \
    --name "$RUNNER_NAME" \
    --labels "${RUNNER_LABELS}:docker://node:lts"
fi
echo ">>> Create config..."
forgejo-runner generate-config > config.yml
sed -i 's#^  force_pull:.*$#  force_pull: true#' config.yml
if [ "$DOCKER_MODE" = "dood" ]; then
  sed -i 's#^  network:.*$#  network: 1panel-network#' config.yml
  sed -i 's#^  docker_host:.*$#  docker_host: unix:///var/run/docker.sock#' config.yml
elif  [ "$DOCKER_MODE" = "dind" ]; then
  sed -i '/envs:/a\    DOCKER_HOST: tcp://dind:2375' config.yml
  sed -i 's#^  docker_host:.*$#  docker_host: tcp://dind:2375#' config.yml
  sed -i 's#^  privileged:.*$#  privileged: true#' config.yml
  sed -i 's#^  options:.*$#  options: --add-host=dind:host-gateway#' config.yml
fi
echo ">>> Starting daemon..."
exec forgejo-runner --config config.yml daemon
