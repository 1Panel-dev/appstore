#!/bin/bash

source ./.env

# 确保宿主机已加载 tun 内核模块：docker-compose 将 /dev/net/tun 设备映射进容器，
# 若宿主机不存在该设备，容器将无法创建。虚拟网络（VirtualNet）依赖此设备。
modprobe tun >/dev/null 2>&1 || true
if [ ! -c /dev/net/tun ]; then
  echo "警告: /dev/net/tun 设备不存在，虚拟网络可能无法正常工作，请确认宿主机已启用 tun 内核模块"
fi


sed -i "s/user = \".*\"/user = \"${CLIENT_NAME}\"/" ./data/frpc.toml
sed -i "s/serverAddr = \".*\"/serverAddr = \"${SERVER_ADDRESS}\"/" ./data/frpc.toml
sed -i "s/serverPort = .*$/serverPort = ${SERVER_PORT}/" ./data/frpc.toml
sed -i "s/auth\.token = \".*\"/auth.token = \"${AUTH_TOKEN}\"/" ./data/frpc.toml
sed -i "s/webServer\.port = .*$/webServer.port = ${PANEL_APP_PORT_HTTP}/" ./data/frpc.toml
sed -i "s/webServer\.user = \".*\"/webServer.user = \"${USER_NAME}\"/" ./data/frpc.toml
sed -i "s/webServer\.password = \".*\"/webServer.password = \"${PASSWORD}\"/" ./data/frpc.toml


if [ "${ENABLE_VIRTUAL_NET}" = "true" ]; then
  # 开启虚拟网络时，虚拟网络IP不能为空且必须为合法 CIDR 格式
  if [ -z "${VIRTUAL_NET_IP}" ]; then
    echo "错误: 已启用虚拟网络，但未填写虚拟网络IP (VIRTUAL_NET_IP)，请填写后重新安装" >&2
    exit 1
  fi
  if ! echo "${VIRTUAL_NET_IP}" | grep -Eq '^([0-9]{1,3}\.){3}[0-9]{1,3}/([0-9]|[12][0-9]|3[0-2])$'; then
    echo "错误: 虚拟网络IP格式不正确，必须为 CIDR 格式（如 10.0.0.1/24）" >&2
    exit 1
  fi
  ip_part="${VIRTUAL_NET_IP%/*}"
  IFS='.' read -r o1 o2 o3 o4 <<< "$ip_part"
  for o in "$o1" "$o2" "$o3" "$o4"; do
    if [ "$o" -lt 0 ] || [ "$o" -gt 255 ]; then
      echo "错误: 虚拟网络IP地址非法，每个段必须在 0-255 之间" >&2
      exit 1
    fi
  done

  sed -i "s|^# featureGates = { VirtualNet = true }|featureGates = { VirtualNet = true }|" ./data/frpc.toml
  sed -i "s|^# virtualNet.address = \".*\"|virtualNet.address = \"${VIRTUAL_NET_IP}\"|" ./data/frpc.toml
fi
