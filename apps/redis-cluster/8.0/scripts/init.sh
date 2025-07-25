#!/bin/bash

dirs=(data conf logs)
owner="1001:1001"
mode="755"

mkdir data
mkdir logs
for dir in "${dirs[@]}"; do
    chown -R "$owner" "$dir"
    chmod -R "$mode" "$dir"
done
