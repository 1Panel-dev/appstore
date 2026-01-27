#!/bin/bash
set -e

mkdir -p data
cd data

## ----------------------------
## Runner 数据目录及权限设置
## ----------------------------

mkdir -p runner-data
touch runner-data/.runner
touch runner-data/config.yml
mkdir -p runner-data/.cache

# 设置权限为 forgejo-runner 镜像中默认用户（UID 1000）
chown -R 1000:1000 runner-data
chmod 775 runner-data/.runner
chmod 775 runner-data/.cache
chmod g+s runner-data/.runner
chmod g+s runner-data/.cache

## ----------------------------
## Docker-in-Docker 数据目录
## ----------------------------

mkdir -p dind-data

## ----------------------------
## daemon.json 镜像加速配置
## ----------------------------

if [ -f /etc/docker/daemon.json ]; then
  cp /etc/docker/daemon.json ./daemon.json
else
  cat > ./daemon.json <<EOF
{
  "registry-mirrors": [
    "https://docker.1panel.live",
    "https://docker.1ms.run"
  ]
}
EOF
fi

cd ..
chmod +x ./scripts/register.sh
