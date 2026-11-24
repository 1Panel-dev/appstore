#!/bin/bash
# ============================================
# 1Panel 安装前钩子（单容器 6.8.3）：只做主机侧准备
#
# Generated from official installation evidence:
# 官方来源证据（init_source_evidence，按 1panel-appstore-skills 要求逐条列出）：
#   - 端口清单与容器内监听：镜像内 /opt/zcloud/etc/services.json（42 进程定义）与 docker-compose.yml
#   - 宿主目录与属主：镜像 entrypoint.sh 的 P0 阶段（data/mysql、data/shared、proxy/aiteam 挂载点）
#   - CA 生成：本仓 build/1panel/scripts/make-ca.sh（每次安装现生成独立 root+intermediate）
#   - 纳管介质：镜像内 /opt/zcloud/assets/paasdata（Agent/Os），对应 compose 的 paasdata 挂载
#   - 固定 MySQL 口令：产品决策（表单不暴露、装后不可改），见 doc/24 交付说明
#   - 端口占用/SELinux/磁盘门槛：doc/00-1panel-2.4.2-verified-constraints.md 与 doc/17 实测结论
# ============================================
set -uo pipefail

ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
ENV_FILE="$ROOT/.env"
DATA="$ROOT/data"
CONFIG="$DATA/shared/dbaas/zcloud-config"
IMAGES="$DATA/shared/dbaas/soft-install/images"
CORE_IMAGE="docker.io/bethunex/bethunex-core:6.8.3"
PROXY_CN="Pmuz9kic2SYiXU2G1z"
PRESERVE_DIR=${ZCLOUD_PRESERVE_DIR:-/opt/zcloud-preserved}

log() { echo "[init] $*"; }
warn() { echo "[init][WARN] $*" >&2; }

valid_ipv4() {
  local ip="$1" o
  [[ "$ip" =~ ^[0-9]{1,3}(\.[0-9]{1,3}){3}$ ]] || return 1
  local IFS=.
  for o in $ip; do
    [ "$o" -le 255 ] 2>/dev/null || return 1
  done
  return 0
}

mkdir -p "$DATA/mysql" "$DATA/shared" "$CONFIG" "$IMAGES" \
         "$DATA/proxy/log" "$DATA/proxy/images" \
         "$DATA/aiteam/workspace" "$DATA/aiteam/confirm" "$DATA/aiteam/logs" \
         "$DATA/aiteam/webui" "$DATA/aiteam/zcl-data" "$DATA/aiteam/backup" \
         "$DATA/milvus/etcd" "$DATA/milvus/minio" "$DATA/milvus/standalone" \
         "$DATA/shared/dbaas/paasdata"

# 1) 宿主 IP
HOST_IP=$(grep -E '^ZCLOUD_HOST_IP=' "$ENV_FILE" 2>/dev/null | tail -1 | cut -d= -f2- | tr -d "'\"")
if [ -z "$HOST_IP" ]; then
  # 默认路由出口地址优先：hostname -I 在多网卡/带 docker0 的宿主机上经常取到 docker0 或第二个网卡
  HOST_IP=$(ip -4 route get 1.1.1.1 2>/dev/null | awk '{for (i = 1; i <= NF; i++) if ($i == "src") { print $(i + 1); exit }}')
  [ -n "$HOST_IP" ] || HOST_IP=$(hostname -I 2>/dev/null | awk '{print $1}')
fi
[ -n "$HOST_IP" ] || { echo "[init][ERROR] 无法探测宿主 IP，请填写 ZCLOUD_HOST_IP" >&2; exit 1; }
valid_ipv4 "$HOST_IP" || { echo "[init][ERROR] ZCLOUD_HOST_IP 不是 IPv4: $HOST_IP" >&2; exit 1; }
echo "$HOST_IP" > "$CONFIG/host-ip.txt"
log "host ip = $HOST_IP"

# 1.1) 表单参数与宿主资源预检（装下去跑不起来比装不上更难排查）
BIND_IP=$(grep -E '^BIND_IP=' "$ENV_FILE" 2>/dev/null | tail -1 | cut -d= -f2- | tr -d "'\"")
BIND_IP=${BIND_IP:-0.0.0.0}
valid_ipv4 "$BIND_IP" || { echo "[init][ERROR] BIND_IP 不是合法 IPv4: $BIND_IP" >&2; exit 1; }
# 资源限制由 1Panel 负责：安装对话框「高级设置」的 CPU/内存会被 1Panel 渲染成
#   deploy.resources.limits.cpus/memory 并同时注入 CPUS / MEMORY_LIMIT 两个 env。
# 注意：API 的 memoryLimit 单位是**字节**（UI 里按 MB 填，由前端换算）；
# 未填时为 0 = 不限制。compose 里不能再写 cpus/mem_limit，否则与 1Panel 注入的
#   deploy 限制冲突（can't set distinct values on 'cpus' and 'deploy.resources.limits.cpus'）。
# 6.8.3 实测基线：42 进程空载 RSS 7.6GiB（6 Java + 22 Go + MySQL/milvus/VM），比 6.8.2
# 的 22.5G 低（Java 改 Go 后堆少了）；仍留峰值余量，推荐 32G、最低 16G。
MEM=$(grep -E '^MEMORY_LIMIT=' "$ENV_FILE" 2>/dev/null | tail -1 | cut -d= -f2- | tr -d "'\"")
CPUS=$(grep -E '^CPUS=' "$ENV_FILE" 2>/dev/null | tail -1 | cut -d= -f2- | tr -d "'\"")
# 归一成 MB：支持字节数（1Panel）与 52g/40960m 这类带单位写法
MEM_MB=$(echo "$MEM" | awk '{
  v=$0
  if (v == "" || v == "0") { print 0; exit }
  if (v ~ /[gG]$/) { gsub(/[gG]/, "", v); printf "%d", v * 1024; exit }
  if (v ~ /[mM]$/) { gsub(/[mM]/, "", v); printf "%d", v; exit }
  printf "%d", v / 1048576
}')
if [ -z "$MEM" ] || [ "$MEM" = "0" ]; then
  echo "[init][ERROR] 未设置容器内存限制：本应用空载约 8G，必须在安装对话框的「高级设置」里填写内存（建议 32768 MB）与 CPU（建议 16 核）。" >&2
  echo "             0 = 不限制，会让容器吃满宿主内存，因此这里直接阻断。" >&2
  exit 1
fi
if [ -z "$MEM_MB" ] || [ "$MEM_MB" -lt 16384 ] 2>/dev/null; then
  echo "[init][ERROR] 内存限制 ${MEM_MB}MB 不足（建议 32768MB，最低 16384MB）" >&2
  exit 1
fi
if [ -z "$CPUS" ] || [ "$CPUS" = "0" ]; then
  warn "未设置 CPU 限制（高级设置 → CPU），容器将不限制 CPU 核数"
elif [ "$CPUS" -lt 8 ] 2>/dev/null; then
  warn "CPU 限制 ${CPUS} 核偏小（42 个常驻进程，建议 ≥16）"
fi
# 磁盘闸门按应用目录所在文件系统算（1Panel 可装到非默认路径，写死 /opt/1panel/apps 会取空跳过）
FREE_G=$(df -Pk "$ROOT" 2>/dev/null | awk 'NR==2{print int($4/1048576)}')
if [ -n "$FREE_G" ] && [ "$FREE_G" -lt 25 ] 2>/dev/null; then
  echo "[init][ERROR] 应用目录所在文件系统可用 ${FREE_G}G，不足 25G（离线包镜像 8.95G + 首装数据约 6–8G）" >&2
  if [ -d "$PRESERVE_DIR" ]; then
    echo "             已有保留备份（卸载/升级留下，可清理最旧几份）：" >&2
    du -sh "$PRESERVE_DIR"/* 2>/dev/null | sort -h | tail -5 >&2
  fi
  exit 1
fi

# 2) firewalld 只告警
if command -v firewall-cmd >/dev/null 2>&1 && firewall-cmd --state >/dev/null 2>&1; then
  warn "firewalld 正在运行：host 网络端口可能被拦截；请按 Docker 方式放行或关闭"
fi

# 2.1) SELinux enforcing 会让容器写 ./data（MySQL/milvus/proxy 数据）被拒绝
if command -v getenforce >/dev/null 2>&1; then
  SE=$(getenforce 2>/dev/null || echo Disabled)
  if [ "$SE" = "Enforcing" ]; then
    echo "[init][ERROR] 宿主 SELinux 为 Enforcing：容器 bind 挂载写入会被拒绝（MySQL/milvus/proxy 数据目录）。" >&2
    echo "             处理方式一：setenforce 0 并把 /etc/selinux/config 改为 SELINUX=permissive 后重装；" >&2
    echo "             处理方式二：在本应用 docker-compose.yml 的 security_opt 增加 label:disable 后重装。" >&2
    exit 1
  fi
fi

# 3) host 端口预检：8080/3306 被非本应用进程占用时阻断
HARD_BLOCK=""
for p in 8080 3306 8200 8211 8202 8203 8204 8205 8212 8214 8215 8216 8217 8218 8219 8220 8221 9070 9914 9915; do
  LINE=$(ss -lntp 2>/dev/null | awk -v re="[:.]${p}\$" '$4 ~ re {print; exit}')
  [ -n "$LINE" ] || continue
  case "$LINE" in
    # 本应用容器与 1Panel 的 docker-proxy 不算占用（容器名由 ${CONTAINER_NAME} 渲染，
    # 可能是安装名或 1Panel-<key>-xxx，故同时匹配 1Panel/bethunex/zcloud）
    *docker-proxy*|*zcloud*|*1Panel*|*bethunex*) continue ;;
  esac
  if [ "$p" = "8080" ] || [ "$p" = "3306" ]; then
    HARD_BLOCK="${HARD_BLOCK}${LINE}"$'\n'
  else
    warn "端口 ${p} 已被占用——单容器启动可能失败：${LINE}"
  fi
done
if [ -n "$HARD_BLOCK" ]; then
  echo "[init][ERROR] 8080/3306 已被非 zCloud 进程占用，安装中止：" >&2
  printf '%s' "$HARD_BLOCK" >&2
  exit 1
fi

# 4) 离线镜像
OFFLINE_IMG=0
if [ -d "$ROOT/images" ]; then
  for f in "$ROOT"/images/*.tar*; do
    [ -f "$f" ] || continue
    log "加载镜像 $(basename "$f")"
    docker load -i "$f" || { echo "[init][ERROR] 加载镜像失败: $f" >&2; exit 1; }
    OFFLINE_IMG=1
  done
fi
# 商店形态：包内不带镜像，装前从镜像仓库拉。
# 必须无条件 pull 而不是"缺失才拉"：同名 tag 重建后本地旧镜像不会自动更新，
# 否则装到的还是上一次的镜像。
if [ "$OFFLINE_IMG" = "0" ]; then
  log "从仓库拉取/更新镜像 $CORE_IMAGE"
  docker pull "$CORE_IMAGE" \
    || { echo "[init][ERROR] 拉取镜像失败: $CORE_IMAGE（检查仓库地址与登录凭据）" >&2; exit 1; }
fi
# 4.1) 镜像形态与模板必须一致：single 模板配 multi 镜像会重复起 MySQL/proxy/milvus
IMG_MODE=$(docker run --rm --entrypoint cat "$CORE_IMAGE" /opt/zcloud/etc/container-mode 2>/dev/null | tr -d '[:space:]')
if [ "$IMG_MODE" != "single" ]; then
  echo "[init][ERROR] 镜像 $CORE_IMAGE 的 container-mode=${IMG_MODE:-未知}，本模板需要 single（用 build-core.sh --single-container 构建）" >&2
  exit 1
fi

# 5) core CA
CA_OK=1
for f in logo.png server.p12 server.key; do
  [ -s "$IMAGES/$f" ] || CA_OK=0
done
# proxy 证书与 core 证书必须来自同一套 CA：任一缺失就整体重建，避免 CA 不一致
[ -s "$DATA/proxy/images/zcloud.png" ] || CA_OK=0
if [ "$CA_OK" != "1" ]; then
  log "生成证书（core + proxy）"
  # --per-install-ca：每次安装生成独立 root+intermediate，包内不再带 CA 私钥（商店分发要求）；
  # --proxy-out/--proxy-cn：proxy 证书用同一套 CA 一起签，CA 私钥不落盘复用
  bash "$ROOT/scripts/make-ca.sh" --image "$CORE_IMAGE" --ca-dir "$ROOT/ca" \
    --output "$IMAGES" --host-ip "$HOST_IP" --force --per-install-ca \
    --proxy-out "$DATA/proxy" --proxy-cn "$PROXY_CN" \
    || { echo "[init][ERROR] core 证书生成失败" >&2; exit 1; }
fi

# 6) proxy 独立证书
#    由上面 make-ca.sh 的 --proxy-out 一并生成（同一套 CA）；这里只做兜底校验
[ -s "$DATA/proxy/images/zcloud.png" ] || {
  echo "[init][ERROR] 缺少 proxy 证书 $DATA/proxy/images/zcloud.png" >&2; exit 1; }
: > "$DATA/proxy/jvm.cfg.user"

# 7) aiteam 配置
# 6.8.3 起 aiteam 的模板（/app/config.json.default）与 aiteam-render-config 都在镜像里，
# 包内不再携带 aiteam/ 目录：直接用镜像内模板渲染，避免包与镜像版本漂移。
if [ -d "$DATA/aiteam/config.json" ]; then
  # 历史上渲染失败可能把挂载点留下了目录，必须先清掉
  rm -rf "$DATA/aiteam/config.json"
fi
if [ ! -f "$DATA/aiteam/config.json" ]; then
  docker run --rm -v "$DATA/aiteam:/data" \
    -e AI_ZCLOUD_IP="$HOST_IP" -e AI_MILVUS_URI="http://127.0.0.1:19530" \
    --entrypoint /usr/local/Python3.9/bin/python3 "$CORE_IMAGE" \
    /opt/rootfs/aiteam/usr/local/bin/aiteam-render-config \
    --out /data/config.json --template /opt/rootfs/aiteam/app/config.json.default \
    || { echo "[init][ERROR] aiteam 配置渲染失败（检查 core 镜像内 aiteam rootfs）" >&2; exit 1; }
  chmod 600 "$DATA/aiteam/config.json"
fi
# 渲染配置：把 __MILVUS_URI__ / __ZCLOUD_IP__ 等占位符替换为实际值（aiteam 首启必需）
if [ -f "$DATA/aiteam/config.json" ]; then
  docker run --rm -v "$DATA/aiteam:/data" \
    -e AI_ZCLOUD_IP="$HOST_IP" -e AI_MILVUS_URI="http://127.0.0.1:19530" \
    --entrypoint /usr/local/Python3.9/bin/python3 "$CORE_IMAGE" \
    /opt/rootfs/aiteam/usr/local/bin/aiteam-render-config \
    --out /data/config.json --template /opt/rootfs/aiteam/app/config.json.default \
    || { echo "[init][ERROR] aiteam 配置渲染失败（检查 core 镜像内 aiteam rootfs）" >&2; exit 1; }
  # 单容器内 nginx 与 aiteam 同网络命名空间：8765/18890 只绑回环
  docker run --rm -v "$DATA/aiteam:/data" --entrypoint /usr/local/Python3.9/bin/python3 "$CORE_IMAGE" -c '
import json
p = "/data/config.json"
c = json.load(open(p))
c.setdefault("channels", {}).setdefault("websocket", {})["host"] = "127.0.0.1"
c.setdefault("gateway", {})["host"] = "127.0.0.1"
json.dump(c, open(p, "w"), ensure_ascii=False, indent=2)
' || { echo "[init][ERROR] aiteam host 回环设置失败" >&2; exit 1; }
  # 渲染后必须没有 __占位符__ 且是合法 JSON，否则 aiteam 连不上 milvus/平台
  docker run --rm -v "$DATA/aiteam:/data" --entrypoint /usr/local/Python3.9/bin/python3 "$CORE_IMAGE" -c '
import json, re, sys
raw = open("/data/config.json", encoding="utf-8").read()
bad = sorted(set(re.findall(r"__[A-Z0-9_]+__", raw)))
if bad:
    sys.stderr.write("未渲染的占位符: %s\n" % ",".join(bad)); sys.exit(1)
json.loads(raw)
' || { echo "[init][ERROR] aiteam config.json 仍有未渲染占位符或非法 JSON" >&2; exit 1; }
fi

# 8) agent 介质
#    新包已把 paasdata 烤进镜像，由容器 entrypoint 首次启动时复制到共享卷；
#    这里只兼容老包（应用目录里还带 paasdata 的情况）。
if [ -d "$ROOT/paasdata" ]; then
  cp -a "$ROOT/paasdata/." "$DATA/shared/dbaas/paasdata/" 2>/dev/null || true
fi

chown -R 1000:1000 "$DATA/shared" "$DATA/proxy" 2>/dev/null || true
chown -R 3306:3306 "$DATA/mysql" 2>/dev/null || true
chmod -R 777 "$DATA/milvus" "$DATA/aiteam/workspace" 2>/dev/null || true
log "完成"
