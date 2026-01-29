#!/bin/bash

chmod +x scripts/moltbot-setup
./scripts/moltbot-setup init
chown -R 1000:1000 data
