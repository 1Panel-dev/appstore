#!/bin/bash
set -euo pipefail

# Generated from official installation evidence:
# https://github.com/chaitin/MonkeyCode/blob/main/monkeyai/backend/Dockerfile
# https://release.monkeycode-ai.com/public/monkeyai/server/online/monkeyai-v26111102-online-linux-amd64.tgz
# 官方后端与 RustFS 使用 UID/GID 10001；运行身份、上传临时目录及对象存储必须可写。
mkdir -p data/postgres data/license data/runtime data/import-tmp data/rustfs data/rustfs-logs
chown -R 10001:10001 data/license data/runtime data/import-tmp data/rustfs data/rustfs-logs
chmod 700 data/license data/runtime data/import-tmp data/rustfs data/rustfs-logs

# 1panel-network 是共享网络；使用当前实例的容器名，避免解析到其他应用的 backend 别名。
container_name=$(awk -F= '$1 == "CONTAINER_NAME" {sub(/^[^=]*=/, ""); sub(/\r$/, ""); print; exit}' .env)
container_name="${container_name#\"}"
container_name="${container_name%\"}"
container_name="${container_name#\'}"
container_name="${container_name%\'}"
if [[ ! "$container_name" =~ ^[a-zA-Z0-9][a-zA-Z0-9_.-]*$ ]]; then
    echo "无法读取有效的 CONTAINER_NAME，已停止初始化" >&2
    exit 1
fi
sed "s/server backend:8080;/server ${container_name}-backend:8080;/" scripts/nginx.conf > data/nginx.conf
chmod 644 data/nginx.conf
