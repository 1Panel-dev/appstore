#!/bin/bash

set -euo pipefail

ORIGINAL_ENTRYPOINT="/usr/bin/docker-entrypoint.sh"
PLUGINS_DIR="${ZABBIX_PLUGINS_DIR:-/var/lib/zabbix/plugins}"
PLUGIN_LOADER_DIR="${ZABBIX_PLUGIN_LOADER_DIR:-/var/lib/zabbix/plugin_loader}"
WORK_DIR="${ZABBIX_PLUGINS_WORK_DIR:-/var/lib/zabbix/plugin-install}"
MANIFEST_FILE="${ZABBIX_MODULES_INSTALL_DIR:-/usr/share/zabbix/modules}/.plugin-manifest"
PLUGIN_LOADER_STAMP_FILE="${WORK_DIR}/.plugin_loader.sha256"

ensure_php_extract() {
    if php -r "exit(class_exists('PharData') ? 0 : 1);" 2>/dev/null; then
        export PHP_EXTRACT_METHOD="phar"
        return 0
    fi

    if php -r "exit(class_exists('ZipArchive') ? 0 : 1);" 2>/dev/null; then
        export PHP_EXTRACT_METHOD="ziparchive"
        return 0
    fi

    echo "** 错误: PHP 缺少 PharData 和 ZipArchive，无法解压插件包"
    exit 1
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

    if [ -n "${DAEMON_USER:-}" ] && getent passwd "$DAEMON_USER" >/dev/null 2>&1; then
        echo "$DAEMON_USER"
        return 0
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

patch_include_once() {
    local modules_dir="${ZABBIX_MODULES_INSTALL_DIR:-/usr/share/zabbix/modules}"
    find "$modules_dir" -name 'CControllerZabbixDBBackup.php' -print0 2>/dev/null | while IFS= read -r -d '' f; do
        if grep -q 'include \$path' "$f" 2>/dev/null && ! grep -q 'include_once \$path' "$f" 2>/dev/null; then
            echo "** 修补 $(basename "$(dirname "$(dirname "$f")")")/actions/CControllerZabbixDBBackup.php: include → include_once"
            sed -i 's/include \$path/include_once \$path/' "$f"
        fi
    done
}

ACTIVATION_BACKUP_DIR=""

backup_activation_files() {
    local modules_dir="${ZABBIX_MODULES_INSTALL_DIR:-/usr/share/zabbix/modules}"
    ACTIVATION_BACKUP_DIR=$(mktemp -d)

    local count=0
    while IFS= read -r -d '' f; do
        local rel="${f#$modules_dir/}"
        local backup_path="${ACTIVATION_BACKUP_DIR}/${rel}"
        mkdir -p "$(dirname "$backup_path")"
        cp -a "$f" "$backup_path"
        count=$((count + 1))
    done < <(find "$modules_dir" -maxdepth 2 -name 'activation.dat' -print0 2>/dev/null)

    if [ "$count" -gt 0 ]; then
        echo "** 已备份 ${count} 个 activation.dat → ${ACTIVATION_BACKUP_DIR}"
    fi
}

restore_activation_files() {
    local modules_dir="${ZABBIX_MODULES_INSTALL_DIR:-/usr/share/zabbix/modules}"

    if [ -z "${ACTIVATION_BACKUP_DIR:-}" ] || [ ! -d "$ACTIVATION_BACKUP_DIR" ]; then
        return 0
    fi

    set +e

    local restored=0 skipped=0 error_count=0
    while IFS= read -r -d '' f; do
        local rel="${f#$ACTIVATION_BACKUP_DIR/}"
        local target="${modules_dir}/${rel}"

        if [ -f "$target" ]; then
            local target_sum backup_sum
            target_sum=$(sha256sum "$target" 2>/dev/null | awk '{print $1}')
            backup_sum=$(sha256sum "$f" 2>/dev/null | awk '{print $1}')
            if [ -n "$target_sum" ] && [ "$target_sum" = "$backup_sum" ]; then
                skipped=$((skipped + 1))
                continue
            fi
        fi

        mkdir -p "$(dirname "$target")" 2>/dev/null || true

        if cp -a "$f" "$target" 2>/dev/null; then
            local php_user="${ZABBIX_PHP_FPM_USER:-zabbix}"
            chown "${php_user}:${php_user}" "$target" 2>/dev/null || true
            restored=$((restored + 1))
        else
            echo "** 警告: 恢复 activation.dat 失败: ${target}" >&2
            error_count=$((error_count + 1))
        fi
    done < <(find "$ACTIVATION_BACKUP_DIR" -name 'activation.dat' -print0 2>/dev/null)

    if [ "$restored" -gt 0 ] || [ "$skipped" -gt 0 ]; then
        echo "** activation.dat: 恢复 ${restored} 个, 未变化 ${skipped} 个" \
            "${error_count:+(${error_count} 个失败)}"
    fi

    rm -rf "$ACTIVATION_BACKUP_DIR"
    ACTIVATION_BACKUP_DIR=""

    set -e
}

propagate_activation_dat() {
    local modules_dir="${ZABBIX_MODULES_INSTALL_DIR:-/usr/share/zabbix/modules}"
    local source_file=""

    source_file=$(find "$modules_dir" -maxdepth 2 -name 'activation.dat' -print -quit 2>/dev/null)

    if [ -z "$source_file" ] || [ ! -f "$source_file" ]; then
        if [ -f "${PLUGINS_DIR}/activation.dat" ]; then
            source_file="${PLUGINS_DIR}/activation.dat"
            echo "** activation.dat: 从 ${PLUGINS_DIR}/ 读取来源"
        fi
    fi

    if [ -z "$source_file" ] || [ ! -f "$source_file" ]; then
        if [ -n "${PLUGIN_LOADER_DIR:-}" ] && [ -f "${PLUGIN_LOADER_DIR}/activation.dat" ]; then
            source_file="${PLUGIN_LOADER_DIR}/activation.dat"
            echo "** activation.dat: 从 ${PLUGIN_LOADER_DIR}/ 读取来源"
        fi
    fi

    if [ -z "$source_file" ] || [ ! -f "$source_file" ]; then
        echo "** activation.dat: 未找到许可证来源文件"
        echo "**    → 请将 activation.dat 放到 data/plugins/ 目录下后重启容器"
        echo "**    → 或手动复制到各模块目录: zabbix-module-*/activation.dat"
        return 0
    fi

    local source_content
    source_content=$(tr -d ' \n\r' < "$source_file")
    if [ -z "$source_content" ]; then
        echo "** activation.dat: 来源文件内容为空，跳过"
        return 0
    fi

    local php_user
    php_user="$(resolve_zabbix_php_fpm_user)"

    local copied=0 skipped=0
    for mod_dir in "$modules_dir"/zabbix-module-*/; do
        [ -d "$mod_dir" ] || continue

        local target="${mod_dir}activation.dat"
        if [ -f "$target" ]; then
            local target_content
            target_content=$(tr -d ' \n\r' < "$target")
            if [ "$target_content" = "$source_content" ]; then
                skipped=$((skipped + 1))
                continue
            fi
        fi

        cp "$source_file" "$target"
        chmod 644 "$target"
        chown "${php_user}:${php_user}" "$target" 2>/dev/null || true
        copied=$((copied + 1))
    done

    if [ "$copied" -gt 0 ]; then
        echo "** activation.dat: 已传播到 ${copied} 个缺失模块 (跳过 ${skipped} 个已有)"
    fi
}

PHP_FPM_INFO_CACHE=""

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

PARSED_MODULE_DIR=""
PARSED_PLUGIN_NAME=""
parse_module_dir_from_zip_name() {
    local zip_name="$1"

    PARSED_MODULE_DIR=""
    PARSED_PLUGIN_NAME=""

    local version
    version=$(echo "$zip_name" | sed -n 's/.*-\([0-9]\+\.[0-9]\+\.[0-9]\+\)-.*/\1/p')
    if [ -z "$version" ]; then
        return 1
    fi

    local plugin_name
    plugin_name=$(echo "$zip_name" | sed "s/-${version}-.*//")

    PARSED_PLUGIN_NAME="$plugin_name"
    PARSED_MODULE_DIR="zabbix-module-${plugin_name}-${version}"
    return 0
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
    local zip_name method

    zip_name=$(basename "$zip")
    method="${PHP_EXTRACT_METHOD:-phar}"
    echo "** 解压插件包（PHP ${method}）: ${zip_name}"

    if [ "$method" = "phar" ]; then
        php -r "
            try {
                \$phar = new PharData('$zip');
                \$phar->extractTo('$WORK_DIR', null, true);
            } catch (Exception \$e) {
                fwrite(STDERR, \"** 错误: PharData 解压失败: \" . \$e->getMessage() . \"\\n\");
                exit(1);
            }
        " || { echo "** 错误: PHP PharData 解压 ${zip_name} 失败"; exit 1; }
    else
        php -r "
            \$zip = new ZipArchive();
            if (\$zip->open('$zip') !== true) {
                fwrite(STDERR, \"** 错误: 无法打开 $zip\\n\");
                exit(1);
            }
            if (!\$zip->extractTo('$WORK_DIR')) {
                fwrite(STDERR, \"** 错误: 解压失败 $zip\\n\");
                \$zip->close();
                exit(1);
            }
            \$zip->close();
        " || { echo "** 错误: PHP ZipArchive 解压 ${zip_name} 失败"; exit 1; }
    fi
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
    local install_sh
    local has_zip="no"
    local changed_zips=()

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

        local -A current_checksums
        for zip in "${zips[@]}"; do
            local zip_name sum
            zip_name=$(basename "$zip")
            sum=$(sha256sum "$zip" | awk '{print $1}')
            current_checksums["$zip_name"]="$sum"
        done

        local -A installed_checksums
        if [ -f "$MANIFEST_FILE" ]; then
            while IFS='  ' read -r sum name; do
                [ -n "$name" ] && installed_checksums["$name"]="$sum"
            done < "$MANIFEST_FILE"
        fi

        local any_changed=false
        local modules_dir="${ZABBIX_MODULES_INSTALL_DIR:-/usr/share/zabbix/modules}"
        for zip_name in "${!current_checksums[@]}"; do
            local current="${current_checksums[$zip_name]}"
            local installed="${installed_checksums[$zip_name]:-}"

            if parse_module_dir_from_zip_name "$zip_name"; then
                if [ -d "${modules_dir}/${PARSED_MODULE_DIR}" ]; then
                    if [ "$current" != "$installed" ]; then
                        echo "** [更新] ${zip_name} → ${PARSED_MODULE_DIR}/ 已存在，zip 有变化，重新安装"
                        changed_zips+=("$PLUGINS_DIR/$zip_name")
                        any_changed=true
                    else
                        echo "** [跳过] ${zip_name} → ${PARSED_MODULE_DIR}/ 已安装且 zip 未变化"
                    fi
                else
                    echo "** [新增] ${zip_name} → ${PARSED_MODULE_DIR}/ 不存在，首次安装"
                    changed_zips+=("$PLUGINS_DIR/$zip_name")
                    any_changed=true
                fi
            else
                echo "** 警告: 无法解析 ${zip_name} 的插件名/版本号，回退到 SHA256 对比"
                if [ "$current" != "$installed" ]; then
                    changed_zips+=("$PLUGINS_DIR/$zip_name")
                    any_changed=true
                fi
            fi
        done

        if [ "$any_changed" = false ]; then
            echo "** 所有插件包未变化，跳过安装"
            return 0
        fi

        echo "** 检测到 ${#changed_zips[@]}/${#current_checksums[@]} 个插件有变化，增量安装"

        ensure_php_extract
        mkdir -p "$WORK_DIR"

        local changed_scripts=()
        for zip in "${changed_zips[@]}"; do
            local zip_name zip_stem
            zip_name=$(basename "$zip")
            zip_stem="${zip_name%.zip}"

            if [ -d "${WORK_DIR}/${zip_stem}" ]; then
                rm -rf "${WORK_DIR:?}/${zip_stem}"
            fi

            prepare_work_dir "$zip"

            local scr
            if [ -x "${WORK_DIR}/${zip_stem}/install.sh" ]; then
                scr="${WORK_DIR}/${zip_stem}/install.sh"
            else
                scr=$(find "${WORK_DIR}/${zip_stem}" -maxdepth 3 -type f -name 'install.sh' -print -quit 2>/dev/null)
            fi
            if [ -n "$scr" ] && [ -f "$scr" ]; then
                changed_scripts+=("$scr")
            fi
        done

        if [ ${#changed_scripts[@]} -eq 0 ]; then
            echo "** 变化插件未包含 install.sh，跳过"
            save_manifest
            return 0
        fi
    fi

    local install_scripts=()
    if [ "$has_zip" = "yes" ]; then
        install_scripts=("${changed_scripts[@]}")
    else
        install_scripts+=("$install_sh")
    fi

    echo "** 执行 ${#install_scripts[@]} 个插件安装"

    local unzip_wrapper="/usr/local/bin/unzip"
    cat > "$unzip_wrapper" <<'UNZIPWRAPPER'
#!/bin/sh
dest="."
while [ $# -gt 0 ]; do
    case "$1" in
        -d) dest="$2"; shift 2 ;;
        -o|-q) shift ;;
        -*) shift ;;
        *)  zipfile="$1"; shift ;;
    esac
done
if [ -z "${zipfile:-}" ]; then
    echo "unzip: missing file operand" >&2; exit 1
fi
export UNZIP_FILE="$zipfile" UNZIP_DEST="$dest"
php -r '
$zipfile=getenv("UNZIP_FILE");
$dest=getenv("UNZIP_DEST");
$saved=[];
$patterns=["$dest/zabbix-module-*/activation.dat","$dest/activation.dat"];
foreach($patterns as $pat){
    $files=glob($pat) ?: [];
    foreach($files as $f) $saved[$f]=@file_get_contents($f);
}
try{$p=new PharData($zipfile);$p->extractTo($dest,null,true);}
catch(Exception $e){fwrite(STDERR,"unzip error: ".$e->getMessage()."\n");exit(1);}
$n=0;
foreach($saved as $path=>$content){
    if($content===false) continue;
    $dir=dirname($path);
    if(!is_dir($dir)) @mkdir($dir,0777,true);
    @file_put_contents($path,$content);
    $n++;
}
if($n>0) fwrite(STDERR,"** unzip wrapper: 已保护 ".$n." 个 activation.dat\n");
'
UNZIPWRAPPER
    chmod +x "$unzip_wrapper"

    start_temp_php_fpm

    local php_fpm_user
    php_fpm_user="$(resolve_zabbix_php_fpm_user)"
    echo "** PHP-FPM pool 用户: ${php_fpm_user}（install.sh 将用此用户 chown 模块目录）"

    backup_activation_files
    trap restore_activation_files EXIT

    local install_status=0
    for install_sh in "${install_scripts[@]}"; do
        local plugin_work_dir
        plugin_work_dir=$(dirname "$install_sh")

        chmod +x "$install_sh" 2>/dev/null || true

        echo "** 执行 $(basename "$plugin_work_dir")/install.sh -m auto"

        set +e
        (cd "$plugin_work_dir" && PHP_USER="$php_fpm_user" ./install.sh -m auto)
        install_status=$?
        set -e

        if [ "$install_status" -ne 0 ]; then
            echo "** 错误: $(basename "$plugin_work_dir")/install.sh 退出码 ${install_status}"
            restore_activation_files
            exit "$install_status"
        fi

        sleep 1
    done

    restore_activation_files
    trap - EXIT

    rm -f "$unzip_wrapper"
    stop_temp_php_fpm

    propagate_activation_dat

    fix_modules_ownership
    patch_include_once
    save_manifest

    echo "** 插件自动安装完成"
}

save_manifest() {
    mkdir -p "$(dirname "$MANIFEST_FILE")"
    local tmp_manifest
    tmp_manifest=$(mktemp)
    shopt -s nullglob
    local zips=("$PLUGINS_DIR"/*.zip)
    shopt -u nullglob
    for zip in "${zips[@]}"; do
        sha256sum "$zip"
    done | while read -r sum path; do
        echo "$sum  $(basename "$path")"
    done > "$tmp_manifest"
    mv "$tmp_manifest" "$MANIFEST_FILE"
}

if [ "$(id -u)" = "0" ]; then
    install_mounted_plugin_loader_if_needed || true
    install_plugins_if_needed || true
    propagate_activation_dat
    fix_modules_ownership
else
    echo "** 非 root 用户，跳过插件自动安装（请使用 compose.plugin-loader.yaml 中的 user: \"0\"）"
fi

exec "$ORIGINAL_ENTRYPOINT" "$@"
