#!/bin/bash
# =============================================================================
# Zabbix 卸载脚本
# 注意：此脚本在容器停止后执行
# =============================================================================

set -e

# ── 自动修复脚本执行权限 ──
SCRIPTS_DIR="$(cd "$(dirname "$0")" && pwd)"
chmod +x "${SCRIPTS_DIR}"/*.sh 2>/dev/null || true

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DATA_DIR="${APP_DIR}/data"

echo "========================================="
echo "[Zabbix Uninstall] 开始清理..."
echo "========================================="

# 注意：为了保护用户数据安全，卸载时不会自动删除数据目录
# 如需彻底删除，请手动执行: rm -rf ${DATA_DIR}
if [ -d "${DATA_DIR}" ]; then
    echo "[Zabbix Uninstall] ⚠ 数据目录未自动删除，保留在: ${DATA_DIR}"
    echo "[Zabbix Uninstall] 如需彻底清除数据，请手动执行: rm -rf ${DATA_DIR}"
else
    echo "[Zabbix Uninstall] 数据目录不存在，无需清理"
fi

echo "[Zabbix Uninstall] 卸载完成"
