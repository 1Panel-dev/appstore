#!/bin/bash
#
# 1Panel 安装初始化脚本
#
# Generated from official installation evidence:
#   - Dockerfile（仓库根）：`addgroup -g 1000 -S app` / `adduser -u 1000 -S app -G app`
#     → 容器运行时 UID/GID 固定为 1000:1000
#   - deploy/1panel/apps/pay-unify/2.0.2/docker-compose.yml：
#     volumes `./data/certs:/app/runtime/certs`、`./data/logs:/app/logs`
#     → 两个宿主目录必须对 uid 1000 可写
#
# 依据：应用镜像以非 root 用户（uid/gid 1000）运行，
#      而宿主机挂载目录 data/certs、data/logs 由 1Panel 以 root 创建，
#      容器内非 root 进程无法写入 → 必须在容器启动前修正属主。
#
# 1Panel 会以「应用安装目录」为工作目录执行本脚本。
set -euo pipefail

cd "$(dirname "$0")/.."

mkdir -p data/certs data/logs
chown -R 1000:1000 data/certs data/logs
chmod 750 data/certs data/logs

echo "pay-unify: 已修正 data/certs、data/logs 属主为 1000:1000"
