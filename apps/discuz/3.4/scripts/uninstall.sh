#!/bin/bash

if docker compose version >/dev/null 2>&1; then
    docker compose down --volumes
elif docker-compose version >/dev/null 2>&1; then
    docker-compose down --volumes
else
    echo "Docker Compose command not found" >&2
    exit 127
fi
