#!/bin/bash
set -e

# 创建数据目录并设置权限
mkdir -p data
touch data/.runner
touch data/config.yml
mkdir -p data/.cache

# 设置为 forgejo-runner 镜像中的默认非 root 用户
chown -R 1000:1000 data
chmod 775 data/.runner
chmod 775 data/.cache
chmod g+s data/.runner
chmod g+s data/.cache

chmod +x ./scripts/register.sh

echo "✅ 初始化完成："
echo " - 已创建 ./data 并配置权限"
echo " - 已创建 ./scripts/register.sh 并配置权限"
