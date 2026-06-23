#!/bin/bash
# =============================================================================
# Zabbix 7.0 安装初始化脚本
# 此脚本在 docker-compose up 之前执行
# =============================================================================

set -e

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DATA_DIR="${APP_DIR}/data"

echo "========================================="
echo "[Zabbix Init] 开始初始化数据目录..."
echo "========================================="

# ── 创建所有必要的目录和 .gitkeep 文件 ──
# Server 端
for dir in \
    server/alertscripts \
    server/externalscripts \
    server/export \
    server/modules \
    server/ssh_keys \
    server/ssl/certs \
    server/ssl/keys; do
    mkdir -p "${DATA_DIR}/${dir}"
    [ ! -f "${DATA_DIR}/${dir}/.gitkeep" ] && touch "${DATA_DIR}/${dir}/.gitkeep"
    echo "[Zabbix Init] ✓ ${dir}"
done

# 确保 export 目录可写
chmod -R 755 "${DATA_DIR}/server/export" 2>/dev/null || true

# Web 端（前端插件/模块）
for dir in web/ssl modules plugins plugin_loader; do
    mkdir -p "${DATA_DIR}/${dir}"
    [ ! -f "${DATA_DIR}/${dir}/.gitkeep" ] && touch "${DATA_DIR}/${dir}/.gitkeep"
    echo "[Zabbix Init] ✓ ${dir}"
done

# modules 目录需要可写（插件安装、activation.dat 写入）
# plugins 也需要 rw（容器内 stamp 文件写入）
chmod -R 777 "${DATA_DIR}/modules" 2>/dev/null || true
chmod -R 777 "${DATA_DIR}/plugins" 2>/dev/null || true
chmod -R 777 "${DATA_DIR}/plugin_loader" 2>/dev/null || true
chmod 777 "${DATA_DIR}" 2>/dev/null || true

# Agent 端
for dir in \
    agent/zabbix_agentd.d \
    agent/user_scripts; do
    mkdir -p "${DATA_DIR}/${dir}"
    [ ! -f "${DATA_DIR}/${dir}/.gitkeep" ] && touch "${DATA_DIR}/${dir}/.gitkeep"
    echo "[Zabbix Init] ✓ ${dir}"
done

# Proxy 端
for dir in \
    proxy/externalscripts \
    proxy/modules \
    proxy/ssh_keys \
    proxy/ssl/certs \
    proxy/ssl/keys; do
    mkdir -p "${DATA_DIR}/${dir}"
    [ ! -f "${DATA_DIR}/${dir}/.gitkeep" ] && touch "${DATA_DIR}/${dir}/.gitkeep"
    echo "[Zabbix Init] ✓ ${dir}"
done

# ── 清理 HA 残留节点（用 docker 跑，不依赖宿主机 mysql 客户端） ──
DB_HOST="${ZABBIX_DB_HOST}"
DB_PORT="${ZABBIX_DB_PORT:-3306}"
echo "[Zabbix Init] 清理 HA 残留节点..."
docker run --rm --pull never --network host \
    zabbix/zabbix-server-mysql:ubuntu-7.0-latest \
    mysql -h"${DB_HOST}" -P"${DB_PORT}" \
        -u"${ZABBIX_DB_USER}" -p"${ZABBIX_DB_PASSWORD}" \
        -e "DELETE FROM ha_node;" "${ZABBIX_DB_NAME}" 2>/dev/null && \
    echo "[Zabbix Init] ✓ HA 节点已清理" || \
    echo "[Zabbix Init] ⚠ HA 清理跳过（首次装无此表，正常）"

# ── 验证数据库连通性（可选） ──
if command -v mysqladmin &> /dev/null; then
    echo "[Zabbix Init] 检查数据库连接 (${DB_HOST}:${DB_PORT})..."
    if mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT}" \
        -u"${ZABBIX_DB_USER}" -p"${ZABBIX_DB_PASSWORD}" --silent 2>/dev/null; then
        echo "[Zabbix Init] ✓ 数据库连接成功"
    else
        echo "[Zabbix Init] ⚠ 无法连接数据库，db-init 容器启动时将自动重试"
    fi
else
    echo "[Zabbix Init] 跳过数据库连接检查，db-init 容器将负责初始化"
fi

echo "========================================="
echo "[Zabbix Init] 初始化完成，目录结构:"
find "${DATA_DIR}" -maxdepth 3 -type d | sed "s|${DATA_DIR}/|  |"
echo "========================================="
