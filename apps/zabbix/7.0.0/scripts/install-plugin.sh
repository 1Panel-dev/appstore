#!/bin/bash
# =============================================================================
# Zabbix 插件安装脚本
# 用法: 把插件 zip 放到 data/ 目录下，然后直接运行:
#   ./install-plugin.sh
# =============================================================================

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
APP_DIR="${SCRIPT_DIR}/.."
DATA_DIR="${APP_DIR}/data"
PLUGIN_LOADER_DIR="${DATA_DIR}/plugin_loader"
MODULES_DIR="${DATA_DIR}/modules"

# 在 data/ 下找插件 zip
ZIP=$(find "$DATA_DIR" -maxdepth 1 -name "zabbix-plugins*.zip" | head -1)

if [ -z "$ZIP" ]; then
    echo "✗ 未在 $DATA_DIR 下找到 zabbix-plugins*.zip"
    echo "  请先把插件 zip 放入 $DATA_DIR/"
    exit 1
fi

echo "→ 发现插件: $(basename "$ZIP")"

WORK_DIR="$(mktemp -d)"
echo "→ 解压 → $WORK_DIR"
unzip -qo "$ZIP" -d "$WORK_DIR"

# 1) 安装 plugin_loader.so
LOADER_SO=$(find "$WORK_DIR" -maxdepth 2 -name "plugin_loader.so" | head -1)
if [ -n "$LOADER_SO" ]; then
    mkdir -p "$PLUGIN_LOADER_DIR"
    cp "$LOADER_SO" "$PLUGIN_LOADER_DIR/"
    echo "✓ plugin_loader.so → $PLUGIN_LOADER_DIR"
else
    echo "⚠ 未找到 plugin_loader.so"
fi

# 2) 找内层 zip（如 unsupported-items-*-zabbix.*.zip）
INNER_ZIP=$(find "$WORK_DIR" -maxdepth 3 -name "*.zip" ! -path "$WORK_DIR" -not -name "$(basename "$ZIP")" | head -1)
if [ -n "$INNER_ZIP" ]; then
    echo "→ 解压内层: $(basename "$INNER_ZIP")"
    unzip -qo "$INNER_ZIP" -d "$WORK_DIR/inner"

    # 3) 找模块 zip（zabbix-module-*.zip）
    MODULE_ZIP=$(find "$WORK_DIR/inner" -maxdepth 2 -name "zabbix-module-*.zip" | head -1)
    if [ -n "$MODULE_ZIP" ]; then
        MODULE_DIR_NAME=$(basename "$MODULE_ZIP" .zip)
        echo "→ 解压模块: $(basename "$MODULE_ZIP")"
        mkdir -p "$WORK_DIR/module"
        unzip -qo "$MODULE_ZIP" -d "$WORK_DIR/module/$MODULE_DIR_NAME"

        # 4) 安装到 data/modules/
        mkdir -p "$MODULES_DIR"
        rm -rf "$MODULES_DIR/$MODULE_DIR_NAME" 2>/dev/null || true
        mkdir -p "$WORK_DIR/module_extract"
        unzip -qo "$MODULE_ZIP" -d "$WORK_DIR/module_extract"
        if [ -d "$WORK_DIR/module_extract/$MODULE_DIR_NAME" ]; then
            mv "$WORK_DIR/module_extract/$MODULE_DIR_NAME" "$MODULES_DIR/"
        else
            mv "$WORK_DIR/module_extract" "$MODULES_DIR/$MODULE_DIR_NAME"
        fi
        chmod -R 777 "$MODULES_DIR/$MODULE_DIR_NAME"
        echo "✓ 模块已安装 → $MODULES_DIR/$MODULE_DIR_NAME"
    else
        echo "⚠ 内层未找到 zabbix-module-*.zip"
    fi
else
    echo "⚠ 未找到内层 zip"
fi

# 5) 清理 HA 残留（避免 Server 异常重启）
echo "→ 清理 HA 残留节点..."
ENVFILE="${APP_DIR}/.env"
if [ -f "$ENVFILE" ]; then
    HOST=$(grep ZABBIX_DB_HOST "$ENVFILE" | cut -d= -f2 | tr -d "'" | xargs)
    USER=$(grep ZABBIX_DB_USER "$ENVFILE" | cut -d= -f2 | tr -d "'" | xargs)
    PASS=$(grep ZABBIX_DB_PASSWORD "$ENVFILE" | cut -d= -f2 | tr -d "'" | xargs)
    DB=$(grep ZABBIX_DB_NAME "$ENVFILE" | cut -d= -f2 | tr -d "'" | xargs)
    mysql -h"$HOST" -u"$USER" -p"$PASS" -e "DELETE FROM ha_node;" "$DB" 2>/dev/null && echo "✓ 已清理" || true
fi

# 6) 重启 Web 容器
echo "→ 重启 Web 容器..."
cd "$APP_DIR"
docker-compose restart zabbix-web 2>/dev/null && echo "✓ Web 已重启" || echo "⚠ 手动重启: cd $APP_DIR && docker compose restart zabbix-web"

# 清理
rm -rf "$WORK_DIR"

echo "========================================="
echo "安装完成. 检查: docker logs zabbix-web"
echo "========================================="
