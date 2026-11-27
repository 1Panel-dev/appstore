#!/bin/bash

# Generated from official installation evidence:
# - https://github.com/nexu-io/open-design/blob/open-design-v0.27.1/deploy/Dockerfile#L84-L86
# - https://github.com/nexu-io/open-design/blob/open-design-v0.27.1/deploy/Dockerfile#L103-L113

# Adjust host-mounted data directory permissions for the container runtime user.
if [ -d data ]; then
    chown -R 1001:1001 data
fi
