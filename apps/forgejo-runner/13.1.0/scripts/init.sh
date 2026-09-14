source .env
mkdir -p data
chown -R 1000:1000 data
if [ "$DOCKER_MODE" = "dood" ]; then
  echo "DOCKER_HOST=unix:///var/run/docker.sock" >> .env
elif  [ "$DOCKER_MODE" = "dind" ]; then
  echo "DOCKER_HOST=tcp://dind:2375" >> .env
fi
chmod +x ./scripts/register.sh