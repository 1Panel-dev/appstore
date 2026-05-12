#!/bin/bash

# SonarQube relies on Elasticsearch, so we keep this kernel tuning as a best effort.
if command -v sysctl >/dev/null 2>&1; then
    if [ -f "/etc/sysctl.conf" ] && ! grep -qxF 'vm.max_map_count=524288' /etc/sysctl.conf 2>/dev/null; then
        printf '\nvm.max_map_count=524288\n' >> /etc/sysctl.conf || true
    fi
    sysctl -w vm.max_map_count=524288 >/dev/null 2>&1 || true
fi

chown -R 1000:1000 data >/dev/null 2>&1 || true
