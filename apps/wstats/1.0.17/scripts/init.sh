#!/bin/bash
# WebStats · 1Panel 安装初始化脚本
#
# 执行时机：安装时、容器启动之前（1Panel 在应用安装目录下以 `bash <安装目录>/scripts/init.sh` 调用）。
# 本脚本尽力而为：任何一步失败都不中断安装。
#
# 做两件事，均有官方来源依据：
#
# 1) 修正相对持久化目录 data/ 的属主
#    依据：官方镜像 Dockerfile（基于 php:8.2-apache）末尾的
#         chown -R www-data:www-data /var/www/html/data
#    Debian 基础镜像中 www-data 的 UID/GID 固定为 33:33。
#    本包把 ./data 绑定挂载到 /var/www/html/data，宿主机目录默认属于 root，
#    而容器内 Apache / worker / cron 都以 www-data 运行，不修正就写不进
#    安装配置（installed.php）、站点验证文件与升级备份。
#
# 2) 补齐随镜像分发的离线 IP 地域库（幂等，已存在则跳过）
#    依据：官方仓库 data/ip2region.xdb（约 10.6MB，IPv4）与 data/ip2region_v6.xdb（约 35.5MB，IPv6）；
#    官方 docker-compose.yml 用的是具名卷 wstat-data:/var/www/html/data，
#    由 Docker 以镜像内容初始化具名卷，因此这两个文件天然存在、地域统计开箱可用。
#    本包按 1Panel 约定改用「相对绑定挂载 ./data」，而绑定挂载不会用镜像内容初始化目录，
#    首次安装时该目录为空，故在此从镜像中取出这两个文件。
#    若本步失败（无 docker 命令 / 镜像不可拉取），安装照常继续，地域功能可稍后在容器内执行
#    `php scripts/fetch-geo.php` 补齐。
set -u

IMAGE="ghcr.io/kfwcc/wstats:latest"
DATA_UID=33
DATA_GID=33

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
APP_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$APP_DIR" 2>/dev/null || true

mkdir -p data
chown -R "${DATA_UID}:${DATA_GID}" data 2>/dev/null || true

have_big_file() {
    [ -f "$1" ] || return 1
    local s
    s="$(wc -c < "$1" 2>/dev/null || echo 0)"
    [ "${s:-0}" -gt 1000000 ]
}

seed_geo() {
    local f="$1" cid=""

    have_big_file "data/$f" && return 0

    if ! command -v docker >/dev/null 2>&1; then
        echo "[wstats-init] 未找到 docker 命令，跳过 data/$f"
        return 0
    fi

    cid="$(docker create "$IMAGE" 2>/dev/null)" || cid=""
    if [ -z "$cid" ]; then
        echo "[wstats-init] 暂时无法使用镜像 ${IMAGE}，跳过 data/$f"
        return 0
    fi

    if docker cp "$cid:/var/www/html/data/$f" "data/$f" 2>/dev/null; then
        echo "[wstats-init] 已补齐离线 IP 库 data/$f"
    else
        echo "[wstats-init] 复制 data/$f 失败，跳过"
    fi

    docker rm -f "$cid" >/dev/null 2>&1 || true
}

seed_geo ip2region.xdb
seed_geo ip2region_v6.xdb

chown -R "${DATA_UID}:${DATA_GID}" data 2>/dev/null || true

echo "[wstats-init] 初始化完成"
