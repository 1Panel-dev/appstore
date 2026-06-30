#!/bin/bash
# =============================================================================
# Zabbix 升级脚本
# 保留数据目录，仅更新容器镜像
# =============================================================================

set -e

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DATA_DIR="${APP_DIR}/data"

echo "========================================="
echo "[Zabbix Upgrade] 开始升级 Zabbix..."
echo "========================================="

# 确保数据目录存在
for dir in server/alertscripts server/externalscripts server/export web/ssl; do
    mkdir -p "${DATA_DIR}/${dir}"
    if [ ! -f "${DATA_DIR}/${dir}/.gitkeep" ]; then
        touch "${DATA_DIR}/${dir}/.gitkeep"
    fi
done

echo "[Zabbix Upgrade] 保留数据目录: ${DATA_DIR}"
echo "[Zabbix Upgrade] 升级完成，将使用新版本镜像"
