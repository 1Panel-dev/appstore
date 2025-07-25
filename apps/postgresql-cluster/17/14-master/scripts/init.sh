#!/bin/bash

dirs=(data postgresql)
owner="1001:1001"
mode="700"

for dir in "${dirs[@]}"; do
    mkdir -p "$dir"
    chown -R "$owner" "$dir"
    chmod -R "$mode" "$dir"
done
