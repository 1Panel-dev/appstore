#!/bin/bash
# 1Panel 在安装 / 升级前会以 root 身份执行本脚本。
#
# gvenzl/oracle-free 镜像以非 root 用户 oracle（UID/GID 54321）运行，
# 而 1Panel 创建的应用目录属于 root，若不做处理，首次启动会报：
#   ERROR: Cannot create folder : errno=13 : Permission denied : /opt/oracle/oradata/FREE
# 所以这里提前把数据目录的属主交给容器用户。

set -e

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

mkdir -p "${APP_DIR}/data" "${APP_DIR}/initdb"

# 容器内 oracle 用户固定为 54321:54321，用数字指定不依赖宿主机是否存在同名用户
chown -R 54321:54321 "${APP_DIR}/data" "${APP_DIR}/initdb"
chmod 775 "${APP_DIR}/data" "${APP_DIR}/initdb"

# SELinux 开启的宿主机（CentOS / RHEL / Oracle Linux）需要打容器标签，失败不影响安装
if command -v selinuxenabled >/dev/null 2>&1 && selinuxenabled; then
  chcon -Rt container_file_t "${APP_DIR}/data" "${APP_DIR}/initdb" 2>/dev/null || true
fi

echo "oracle: data/initdb 目录权限已设置为 54321:54321"
