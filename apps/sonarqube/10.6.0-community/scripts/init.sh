#!/bin/bash

if [ -f "/etc/sysctl.conf" ]; then
    grep -qxF 'vm.max_map_count=262144' /etc/sysctl.conf || echo 'vm.max_map_count=262144' >> /etc/sysctl.conf
    sysctl -p /etc/sysctl.conf >/dev/null
else
    sysctl -w vm.max_map_count=262144
fi

chown -R 1000:1000 data