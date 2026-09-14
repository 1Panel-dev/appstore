#!/bin/bash
set -euo pipefail

# Generated from official installation evidence: https://github.com/zaigie/palworld-server-tool/blob/v0.12.2/README.md
mkdir -p data/backups
touch data/pst.db data/config.db
chmod 600 data/pst.db data/config.db
