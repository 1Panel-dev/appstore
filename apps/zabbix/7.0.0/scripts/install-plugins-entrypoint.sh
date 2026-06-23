#!/bin/bash
# Zabbix Web 容器入口：启动前自动安装 plugins 目录中的插件合集包
# 1. 可选：从挂载目录安装 plugin_loader.so（data/plugin_loader/）
# 2. 检测 *.zip → 解压 → 执行 ./install.sh -m auto（安装模块与 plugin_loader.so）
# 完成后委托官方 docker-entrypoint.sh 启动 Nginx + PHP-FPM

set -euo pipefail

ORIGINAL_ENTRYPOINT="/usr/bin/docker-entrypoint.sh"
PLUGINS_DIR="${ZABBIX_PLUGINS_DIR:-/var/lib/zabbix/plugins}"
PLUGIN_LOADER_DIR="${ZABBIX_PLUGIN_LOADER_DIR:-/var/lib/zabbix/plugin_loader}"
WORK_DIR="${ZABBIX_PLUGINS_WORK_DIR:-/var/lib/zabbix/plugin-install}"
STAMP_FILE="${WORK_DIR}/.installed.sha256"
PLUGIN_LOADER_STAMP_FILE="${WORK_DIR}/.plugin_loader.sha256"

ensure_unzip() {
    if command -v unzip >/dev/null 2>&1; then
        return 0
    fi

    echo "** unzip 未安装，正在安装（仅首次需要网络）..."

    if command -v apt-get >/dev/null 2>&1; then
        export DEBIAN_FRONTEND=noninteractive
        apt-get update -qq
        apt-get install -y -qq --no-install-recommends unzip
        rm -rf /var/lib/apt/lists/*
    elif command -v microdnf >/dev/null 2>&1; then
        microdnf install -y unzip
    elif command -v dnf >/dev/null 2>&1; then
        dnf install -y unzip
    elif command -v yum >/dev/null 2>&1; then
        yum install -y unzip
    elif command -v apk >/dev/null 2>&1; then
        apk add --no-cache unzip
    else
        echo "** 错误: 未找到支持的包管理器，无法安装 unzip"
        return 1
    fi
}

export_php_fpm_env_defaults() {
    export PHP_FPM_PM="${PHP_FPM_PM:-dynamic}"
    export PHP_FPM_PM_MAX_CHILDREN="${PHP_FPM_PM_MAX_CHILDREN:-50}"
    export PHP_FPM_PM_START_SERVERS="${PHP_FPM_PM_START_SERVERS:-5}"
    export PHP_FPM_PM_MIN_SPARE_SERVERS="${PHP_FPM_PM_MIN_SPARE_SERVERS:-5}"
    export PHP_FPM_PM_MAX_SPARE_SERVERS="${PHP_FPM_PM_MAX_SPARE_SERVERS:-35}"
    export PHP_FPM_PM_MAX_REQUESTS="${PHP_FPM_PM_MAX_REQUESTS:-0}"
    export ZBX_MAXEXECUTIONTIME="${ZBX_MAXEXECUTIONTIME:-600}"
    export ZBX_MEMORYLIMIT="${ZBX_MEMORYLIMIT:-128M}"
    export ZBX_POSTMAXSIZE="${ZBX_POSTMAXSIZE:-16M}"
    export ZBX_UPLOADMAXFILESIZE="${ZBX_UPLOADMAXFILESIZE:-2M}"
    export ZBX_MAXINPUTTIME="${ZBX_MAXINPUTTIME:-300}"
    export PHP_TZ="${PHP_TZ:-Europe/Riga}"
    export EXPOSE_WEB_SERVER_INFO="${EXPOSE_WEB_SERVER_INFO:-on}"
}

# 解析 Zabbix Web 容器内 PHP-FPM pool 运行用户（非 master 的 root）
resolve_zabbix_php_fpm_user() {
    local pool_conf pool_user

    if [ -n "${ZABBIX_PHP_FPM_USER:-}" ]; then
        echo "$ZABBIX_PHP_FPM_USER"
        return 0
    fi

    pool_conf=$(ls /etc/php/*/fpm/pool.d/zabbix.conf 2>/dev/null | head -1 || true)
    if [ -n "$pool_conf" ] && [ -f "$pool_conf" ]; then
        pool_user=$(grep -iE '^[[:space:]]*user[[:space:]]*=' "$pool_conf" 2>/dev/null \
            | head -1 | sed 's/^[[:space:]]*user[[:space:]]*=[[:space:]]*//I;s/[[:space:]]*;.*$//;s/^[[:space:]]*//;s/[[:space:]]*$//')
        if [ -n "$pool_user" ]; then
            echo "$pool_user"
            return 0
        fi
    fi

    for candidate in zabbix apache nginx www-data; do
        if getent passwd "$candidate" >/dev/null 2>&1; then
            echo "$candidate"
            return 0
        fi
    done

    echo "www-data"
}

fix_modules_ownership() {
    local modules_dir="${ZABBIX_MODULES_INSTALL_DIR:-/usr/share/zabbix/modules}"
    local php_user

    php_user="$(resolve_zabbix_php_fpm_user)"
    if [ ! -d "$modules_dir" ]; then
        return 0
    fi

    echo "** 修正模块目录所有权为 ${php_user}（供 Web 写入 activation.dat）"
    chown -R "${php_user}:${php_user}" "$modules_dir"
    chmod -R u+rwX,g+rX "$modules_dir"
}

PHP_FPM_INFO_CACHE=""

# php-fpm -i 输出较长；若与 awk exit 组成管道，pipefail 下会因 SIGPIPE 导致退出码 255
php_fpm_info() {
    if [ -n "$PHP_FPM_INFO_CACHE" ]; then
        echo "$PHP_FPM_INFO_CACHE"
        return 0
    fi

    local php_fpm_bin="${PHP_FPM_BIN:-}"
    if [ -z "$php_fpm_bin" ]; then
        resolve_php_fpm_paths
        php_fpm_bin="${PHP_FPM_BIN:-}"
    fi

    if [ -n "$php_fpm_bin" ] && [ -x "$php_fpm_bin" ]; then
        PHP_FPM_INFO_CACHE=$("$php_fpm_bin" -i 2>/dev/null || true)
    fi

    echo "$PHP_FPM_INFO_CACHE"
}

resolve_php_fpm_paths() {
    local php_ver php_fpm_bin php_fpm_conf

    php_fpm_bin="$(command -v php-fpm 2>/dev/null || true)"
    if [ -z "$php_fpm_bin" ]; then
        php_fpm_bin="$(ls /usr/sbin/php-fpm* 2>/dev/null | head -1 || true)"
    fi

    php_ver="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || true)"
    if [ -n "$php_ver" ] && [ -f "/etc/php/${php_ver}/fpm/php-fpm.conf" ]; then
        php_fpm_conf="/etc/php/${php_ver}/fpm/php-fpm.conf"
    elif [ -f /etc/php-fpm.conf ]; then
        php_fpm_conf="/etc/php-fpm.conf"
    else
        php_fpm_conf="$(ls /etc/php/*/fpm/php-fpm.conf 2>/dev/null | head -1 || true)"
    fi

    PHP_FPM_BIN="$php_fpm_bin"
    PHP_FPM_CONF="$php_fpm_conf"
}

start_temp_php_fpm() {
    if pgrep -f 'php-fpm: master' >/dev/null 2>&1; then
        return 0
    fi

    export_php_fpm_env_defaults
    resolve_php_fpm_paths

    if [ -z "${PHP_FPM_BIN:-}" ] || [ -z "${PHP_FPM_CONF:-}" ]; then
        echo "** 警告: 未找到 php-fpm 可执行文件或配置，跳过临时启动"
        return 0
    fi

    echo "** 临时启动 PHP-FPM（${PHP_FPM_BIN}），供 install.sh 检测运行环境"
    local -a fpm_args=(--daemonize -y "$PHP_FPM_CONF")
    if [ "$(id -u)" = "0" ]; then
        fpm_args=(--allow-to-run-as-root "${fpm_args[@]}")
    fi
    if ! "$PHP_FPM_BIN" "${fpm_args[@]}"; then
        echo "** 警告: PHP-FPM 临时启动失败，install.sh 将尝试其他检测方式"
        return 0
    fi
    sleep 2
}

stop_temp_php_fpm() {
    if [ -f /tmp/php-fpm.pid ]; then
        kill "$(cat /tmp/php-fpm.pid)" 2>/dev/null || true
        rm -f /tmp/php-fpm.pid
    fi
    pkill -f 'php-fpm: master' 2>/dev/null || true
    pkill -x php-fpm 2>/dev/null || true
    rm -f /tmp/php-fpm.sock 2>/dev/null || true
    sleep 1
}

plugins_zip_checksum() {
    local checksum_file="$1"
    shopt -s nullglob
    local zips=("$PLUGINS_DIR"/*.zip)
    shopt -u nullglob

    if [ ${#zips[@]} -eq 0 ]; then
        : > "$checksum_file"
        return 0
    fi

    for zip in "${zips[@]}"; do
        sha256sum "$zip"
    done | sort > "$checksum_file"
}

find_install_script() {
    local root="$1"

    if [ -x "${root}/install.sh" ]; then
        echo "${root}/install.sh"
        return 0
    fi

    local install_sh
    install_sh=$(find "$root" -maxdepth 3 -type f -name 'install.sh' 2>/dev/null | head -1)
    if [ -n "$install_sh" ] && [ -f "$install_sh" ]; then
        chmod +x "$install_sh" 2>/dev/null || true
        echo "$install_sh"
        return 0
    fi

    return 1
}

prepare_work_dir() {
    local zip="$1"
    local zip_name

    zip_name=$(basename "$zip")
    echo "** 解压插件包: ${zip_name}"
    unzip -oq "$zip" -d "$WORK_DIR"
}

resolve_php_bin() {
    if [ -n "${PHP_BIN:-}" ] && [ -x "$PHP_BIN" ]; then
        return 0
    fi

    PHP_BIN="$(command -v php 2>/dev/null || true)"
}

resolve_php_fpm_ini_for_loader() {
    local phpinfo

    PHP_INI=""
    phpinfo="$(php_fpm_info)"
    if [ -n "$phpinfo" ]; then
        PHP_INI=$(echo "$phpinfo" | awk -F'=> ' '/^Loaded Configuration File/{gsub(/^[[:space:]]+|[[:space:]]+$/,"",$2); print $2; exit}')
    fi

    if [ -z "$PHP_INI" ] || [ ! -f "$PHP_INI" ]; then
        local candidate
        for candidate in /etc/php/*/fpm/php.ini /etc/php.ini; do
            for candidate in $candidate; do
                if [ -f "$candidate" ]; then
                    PHP_INI="$candidate"
                    return 0
                fi
            done
        done
    fi

    resolve_php_bin
    if [ -z "$PHP_INI" ] && [ -n "$PHP_BIN" ]; then
        PHP_INI=$("$PHP_BIN" -r "echo php_ini_loaded_file();" 2>/dev/null || true)
    fi
}

resolve_php_ext_dir_for_loader() {
    PHP_EXT_DIR=""

    resolve_php_bin
    resolve_php_fpm_ini_for_loader

    if [ -n "$PHP_BIN" ] && [ -n "$PHP_INI" ] && [ -f "$PHP_INI" ]; then
        PHP_EXT_DIR=$("$PHP_BIN" -c "$PHP_INI" -r "echo ini_get('extension_dir');" 2>/dev/null || true)
    fi

    if [ -z "$PHP_EXT_DIR" ]; then
        local phpinfo
        phpinfo="$(php_fpm_info)"
        if [ -n "$phpinfo" ]; then
            PHP_EXT_DIR=$(echo "$phpinfo" | awk -F'=> ' '/^extension_dir /{v=$2; sub(/[[:space:]]*=>.*$/,"",v); gsub(/^[[:space:]]+|[[:space:]]+$/,"",v); print v; exit}')
        fi
    fi

    if [ -z "$PHP_EXT_DIR" ] && [ -n "$PHP_BIN" ]; then
        PHP_EXT_DIR=$("$PHP_BIN" -r "echo ini_get('extension_dir');" 2>/dev/null || true)
    fi
}

ensure_plugin_loader_php_ini() {
    local cli_ini

    if [ -z "${PHP_INI:-}" ] || [ ! -f "$PHP_INI" ]; then
        echo "** 警告: 未找到 PHP-FPM php.ini，跳过 plugin_loader 扩展配置"
        return 1
    fi

    if grep -qE '^[[:space:]]*extension[[:space:]]*=[[:space:]]*plugin_loader\.so' "$PHP_INI" 2>/dev/null; then
        echo "** extension=plugin_loader.so 已在 PHP-FPM php.ini 中配置"
    else
        echo "** 添加 extension=plugin_loader.so 到 PHP-FPM php.ini (${PHP_INI})"
        echo "extension=plugin_loader.so" >> "$PHP_INI"
    fi

    resolve_php_bin
    if [ -n "$PHP_BIN" ]; then
        cli_ini=$("$PHP_BIN" -r "echo php_ini_loaded_file();" 2>/dev/null || true)
        if [ -n "$cli_ini" ] && [ "$cli_ini" != "$PHP_INI" ] && [ -f "$cli_ini" ]; then
            if ! grep -qE '^[[:space:]]*extension[[:space:]]*=[[:space:]]*plugin_loader\.so' "$cli_ini" 2>/dev/null; then
                echo "** 同步 extension=plugin_loader.so 到 CLI php.ini (${cli_ini})"
                echo "extension=plugin_loader.so" >> "$cli_ini"
            fi
        fi
    fi
}

find_mounted_plugin_loader_so() {
    local dir="$1"
    local candidate

    if [ ! -d "$dir" ]; then
        return 1
    fi

    if [ -f "${dir}/plugin_loader.so" ]; then
        echo "${dir}/plugin_loader.so"
        return 0
    fi

    shopt -s nullglob
    local candidates=("${dir}"/plugin_loader-*.so)
    shopt -u nullglob

    if [ ${#candidates[@]} -eq 1 ]; then
        echo "${candidates[0]}"
        return 0
    fi

    if [ ${#candidates[@]} -gt 1 ]; then
        echo "** 警告: ${dir} 中存在多个 plugin_loader-*.so，请仅保留一个或命名为 plugin_loader.so" >&2
        return 1
    fi

    return 1
}

install_mounted_plugin_loader_if_needed() {
    local src target current_checksum installed_checksum

    if [ "${ZABBIX_PLUGIN_LOADER_AUTO_INSTALL:-true}" != "true" ]; then
        echo "** ZABBIX_PLUGIN_LOADER_AUTO_INSTALL=false，跳过挂载目录 plugin_loader.so 安装"
        return 0
    fi

    src=$(find_mounted_plugin_loader_so "$PLUGIN_LOADER_DIR") || return 0

    echo "** 发现挂载的 plugin_loader.so: ${src}"
    resolve_php_fpm_paths
    resolve_php_ext_dir_for_loader

    if [ -z "${PHP_EXT_DIR:-}" ]; then
        echo "** 错误: 无法解析 PHP 扩展目录，跳过 plugin_loader.so 安装"
        return 1
    fi

    target="${PHP_EXT_DIR}/plugin_loader.so"
    current_checksum=$(sha256sum "$src" | awk '{print $1}')

    if [ -f "$PLUGIN_LOADER_STAMP_FILE" ]; then
        installed_checksum=$(cat "$PLUGIN_LOADER_STAMP_FILE")
        if [ "$installed_checksum" = "$current_checksum" ] && [ -f "$target" ]; then
            local target_checksum
            target_checksum=$(sha256sum "$target" | awk '{print $1}')
            if [ "$target_checksum" = "$current_checksum" ]; then
                echo "** 挂载的 plugin_loader.so 未变化，跳过重复安装"
                ensure_plugin_loader_php_ini || true
                return 0
            fi
        fi
    fi

    echo "** 复制 plugin_loader.so 到 ${target}"
    cp -f "$src" "$target"
    chmod 644 "$target"
    ensure_plugin_loader_php_ini || true

    mkdir -p "$(dirname "$PLUGIN_LOADER_STAMP_FILE")"
    echo "$current_checksum" > "$PLUGIN_LOADER_STAMP_FILE"
    echo "** 挂载目录 plugin_loader.so 安装完成"
}

install_plugins_if_needed() {
    local install_sh checksum_file current_checksum
    local has_zip="no"

    if [ "${ZABBIX_PLUGINS_AUTO_INSTALL:-true}" != "true" ]; then
        echo "** ZABBIX_PLUGINS_AUTO_INSTALL=false，跳过插件自动安装"
        return 0
    fi

    if [ ! -d "$PLUGINS_DIR" ]; then
        echo "** 插件目录不存在: ${PLUGINS_DIR}，跳过"
        return 0
    fi

    shopt -s nullglob
    local zips=("$PLUGINS_DIR"/*.zip)
    shopt -u nullglob

    if [ ${#zips[@]} -eq 0 ]; then
        if install_sh=$(find_install_script "$PLUGINS_DIR"); then
            echo "** 未发现 zip，使用已解压目录中的 install.sh"
            WORK_DIR="$PLUGINS_DIR"
        else
            echo "** ${PLUGINS_DIR} 中无 *.zip，跳过插件自动安装"
            return 0
        fi
    else
        has_zip="yes"
        checksum_file=$(mktemp)
        plugins_zip_checksum "$checksum_file"
        current_checksum=$(sha256sum "$checksum_file" | awk '{print $1}')
        rm -f "$checksum_file"

        if [ -f "$STAMP_FILE" ] && [ "$(cat "$STAMP_FILE")" = "$current_checksum" ]; then
            echo "** 插件包未变化，跳过重复安装"
            return 0
        fi

        ensure_unzip
        rm -rf "$WORK_DIR"
        mkdir -p "$WORK_DIR"

        for zip in "${zips[@]}"; do
            prepare_work_dir "$zip"
        done

        install_sh=$(find_install_script "$WORK_DIR") || {
            echo "** 警告: 解压后未找到 install.sh，跳过插件安装"
            return 0
        }
        WORK_DIR=$(dirname "$install_sh")
    fi

    install_sh=$(find_install_script "$WORK_DIR") || {
        echo "** 警告: 未找到 install.sh，跳过插件安装"
        return 0
    }

    start_temp_php_fpm

    local php_fpm_user
    php_fpm_user="$(resolve_zabbix_php_fpm_user)"
    echo "** PHP-FPM pool 用户: ${php_fpm_user}（install.sh 将用此用户 chown 模块目录）"

    echo "** 执行 $(basename "$install_sh") -m auto （目录: ${WORK_DIR}）"
    set +e
    (cd "$WORK_DIR" && PHP_USER="$php_fpm_user" ./install.sh -m auto)
    local install_status=$?
    set -e

    stop_temp_php_fpm

    if [ "$install_status" -ne 0 ]; then
        echo "** 错误: install.sh 退出码 ${install_status}"
        exit "$install_status"
    fi

    fix_modules_ownership

    if [ "$has_zip" = "yes" ]; then
        mkdir -p "$(dirname "$STAMP_FILE")"
        checksum_file=$(mktemp)
        plugins_zip_checksum "$checksum_file"
        sha256sum "$checksum_file" | awk '{print $1}' > "$STAMP_FILE"
        rm -f "$checksum_file"
    fi

    echo "** 插件自动安装完成"
}

if [ "$(id -u)" = "0" ]; then
    install_mounted_plugin_loader_if_needed || true
    install_plugins_if_needed || true
    fix_modules_ownership
else
    echo "** 非 root 用户，跳过插件自动安装（请使用 compose.plugin-loader.yaml 中的 user: \"0\"）"
fi

exec "$ORIGINAL_ENTRYPOINT" "$@"
