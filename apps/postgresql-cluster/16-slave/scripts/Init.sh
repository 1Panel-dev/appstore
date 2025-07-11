#!/bin/bash

dirs=(data postgresql)
owner="1001:1001"
mode="700"

for dir in "${dirs[@]}"; do
    if [ -d "$dir" ]; then
        chown -R "$owner" "$dir"
        chmod -R "$mode" "$dir"
    else
        echo "目录不存在: $dir"
    fi
done
