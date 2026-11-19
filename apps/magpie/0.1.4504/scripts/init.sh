#!/bin/bash

# Generated from official installation evidence:
# - https://github.com/yetone/magpie/blob/main/Dockerfile
# - https://usemagpie.ai/docs/zh/docker

# Adjust host-mounted data directory permissions for the container runtime user.
if [ -d config ]; then
    chown -R 65532:65532 config
fi
