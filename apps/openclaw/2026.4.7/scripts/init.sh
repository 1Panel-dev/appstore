#!/bin/bash
set -e

ARCH=$(uname -m)

case "$ARCH" in
  x86_64|amd64)
    BIN="openclaw-setup-linux-amd64"
    ;;
  aarch64|arm64)
    BIN="openclaw-setup-linux-arm64"
    ;;
  *)
    echo "Unsupported architecture: $ARCH"
    exit 1
    ;;
esac

chmod +x "scripts/${BIN}"
./scripts/${BIN} init

chown -R 1000:1000 data
