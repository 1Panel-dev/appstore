#!/bin/bash

# Generated from official installation evidence:
# - https://github.com/apache/kafka/blob/4.4.0/docker/examples/README.md#installation-preparation

# Adjust host-mounted data directory permissions for the container runtime user.
if [ -d data ]; then
    chown -R 1000:1000 data
fi
