#!/bin/bash

set -e

SCRIPTS_DIR="$(cd "$(dirname "$0")" && pwd)"
echo "[Zabbix Init] 修复脚本执行权限..."
chmod +x "${SCRIPTS_DIR}"/*.sh 2>/dev/null || true
echo "[Zabbix Init] ✓ 脚本权限已修复"

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DATA_DIR="${APP_DIR}/data"

echo "========================================="
echo "[Zabbix Init] 开始初始化数据目录..."
echo "========================================="

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

chmod -R 755 "${DATA_DIR}/server/export" 2>/dev/null || true

for dir in web/ssl modules plugins plugin_loader; do
    mkdir -p "${DATA_DIR}/${dir}"
    [ ! -f "${DATA_DIR}/${dir}/.gitkeep" ] && touch "${DATA_DIR}/${dir}/.gitkeep"
    echo "[Zabbix Init] ✓ ${dir}"
done

chmod -R 777 "${DATA_DIR}/modules" 2>/dev/null || true
chmod -R 777 "${DATA_DIR}/plugins" 2>/dev/null || true
chmod -R 777 "${DATA_DIR}/plugin_loader" 2>/dev/null || true
chmod 777 "${DATA_DIR}" 2>/dev/null || true

for dir in \
    agent/zabbix_agentd.d \
    agent/user_scripts; do
    mkdir -p "${DATA_DIR}/${dir}"
    [ ! -f "${DATA_DIR}/${dir}/.gitkeep" ] && touch "${DATA_DIR}/${dir}/.gitkeep"
    echo "[Zabbix Init] ✓ ${dir}"
done

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
