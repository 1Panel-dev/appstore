#!/bin/bash
# ============================================
# make-ca.sh — 安装时一次性生成证书（宿主侧执行，证书不进镜像）
#
# 为什么不在镜像里放 CA：CA 私钥能签发被 root CA 信任的证书，一旦进入镜像层，
#   `docker save` / `docker history` / 任意容器进程都能取到（评审 P0-2）。
#   因此镜像只保留运行期依赖，证书在安装时用一次性容器现算，输出落共享卷。
#
# 做法与 Swarm 的 03-storage.sh 同款：挂载 CA 目录（来自 1Panel 应用包）
#   到一次性容器，用镜像内的 JRE + openssl 执行 init.sh。
#
# 用法：
#   bash make-ca.sh --image <core 镜像> --ca-dir <应用包内 ca 目录> \
#                   --output <共享卷内 images 目录> --host-ip <宿主 IP> [--force] \
#                   [--per-install-ca]
#
# --per-install-ca：不使用包内预置 CA，而是每次安装现生成独立的 root + intermediate，
#   再用它们签 server/proxy 证书。公开分发（1Panel 商店）必须用这个模式，否则所有部署
#   共用同一把 intermediate 私钥，拿到包的人可为任意部署签发受信任证书。
#
# 退出码：0 生成成功或已存在；非 0 失败（调用方应中止部署）
# ============================================
set -uo pipefail

IMAGE=""; CA_DIR=""; OUT=""; HOST_IP="${HOST_IP:-}"; FORCE="false"; PER_INSTALL="false"
PROXY_OUT=""; PROXY_CN=""
while [ $# -gt 0 ]; do
  case "$1" in
    --image)   [ $# -ge 2 ] || { echo "--image 需要参数" >&2; exit 1; }; IMAGE="$2"; shift 2 ;;
    --ca-dir)  [ $# -ge 2 ] || { echo "--ca-dir 需要参数" >&2; exit 1; }; CA_DIR="$2"; shift 2 ;;
    --output)  [ $# -ge 2 ] || { echo "--output 需要参数" >&2; exit 1; }; OUT="$2"; shift 2 ;;
    --host-ip) [ $# -ge 2 ] || { echo "--host-ip 需要参数" >&2; exit 1; }; HOST_IP="$2"; shift 2 ;;
    --force)   FORCE="true"; shift ;;
    --per-install-ca) PER_INSTALL="true"; shift ;;
    --proxy-out) [ $# -ge 2 ] || { echo "--proxy-out 需要参数" >&2; exit 1; }; PROXY_OUT="$2"; shift 2 ;;
    --proxy-cn)  [ $# -ge 2 ] || { echo "--proxy-cn 需要参数" >&2; exit 1; }; PROXY_CN="$2"; shift 2 ;;
    -h|--help) sed -n '2,18p' "$0"; exit 0 ;;
    *) echo "未知参数: $1" >&2; exit 1 ;;
  esac
done

[ -n "$IMAGE" ]  || { echo "ERROR: 需要 --image" >&2; exit 1; }
[ -n "$CA_DIR" ] || { echo "ERROR: 需要 --ca-dir" >&2; exit 1; }
[ -n "$OUT" ]    || { echo "ERROR: 需要 --output" >&2; exit 1; }
[ -n "$HOST_IP" ] || { echo "ERROR: 需要 --host-ip" >&2; exit 1; }
[ -f "$CA_DIR/init.sh" ] || { echo "ERROR: $CA_DIR/init.sh 不存在（应用包缺少 ca 目录？）" >&2; exit 1; }
docker image inspect "$IMAGE" >/dev/null 2>&1 || { echo "ERROR: 镜像不存在: $IMAGE" >&2; exit 1; }

if [ -f "$OUT/logo.png" ] && [ "$FORCE" != "true" ]; then
  echo "证书已存在，跳过（--force 可重建）: $OUT/logo.png"
  exit 0
fi

mkdir -p "$OUT"

# 每次安装生成独立 CA：把包内 ca/ 复制到临时目录，替换掉预置 root/intermediate
CA_TMP=""
cleanup() { [ -n "$CA_TMP" ] && rm -rf "$CA_TMP"; }
trap cleanup EXIT
if [ "$PER_INSTALL" = "true" ]; then
  command -v openssl >/dev/null 2>&1 || { echo "ERROR: 需要宿主机 openssl 生成 CA" >&2; exit 1; }
  CA_TMP=$(mktemp -d /tmp/zcloud-ca.XXXXXX) || { echo "ERROR: mktemp 失败" >&2; exit 1; }
  cp -a "$CA_DIR/." "$CA_TMP/"
  rm -f "$CA_TMP"/root_ca.* "$CA_TMP"/intermediate_ca.*
  STAMP=$(date +%Y%m%d%H%M%S)-$$-$(od -An -N4 -tx4 /dev/urandom | tr -d ' \n')
  echo "==> 生成独立 CA（root + intermediate，序列 $STAMP）"
  # 用 config 文件而不是 -addext：CentOS 7 自带 OpenSSL 1.0.2k 不支持 -addext
  cat > "$CA_TMP/root_ca.cnf" <<EOF
[req]
distinguished_name = dn
x509_extensions = v3_ca
prompt = no
[dn]
C = CN
O = Bethune X
CN = Bethune X Root CA $STAMP
[v3_ca]
basicConstraints = critical,CA:TRUE
keyUsage = critical,keyCertSign,cRLSign
subjectKeyIdentifier = hash
authorityKeyIdentifier = keyid:always
EOF
  # 与预置 CA 同规格（root 8192 / intermediate 4096）：init.sh 会按产物大小做完整性校验
  openssl genrsa -out "$CA_TMP/root_ca.key" 8192 2>/dev/null || exit 1
  openssl req -x509 -new -sha256 -days 3650 -key "$CA_TMP/root_ca.key" \
    -config "$CA_TMP/root_ca.cnf" \
    -out "$CA_TMP/root_ca.cert" || exit 1
  openssl genrsa -out "$CA_TMP/intermediate_ca.key" 4096 2>/dev/null || exit 1
  openssl req -new -sha256 -key "$CA_TMP/intermediate_ca.key" \
    -subj "/C=CN/O=Bethune X/CN=Bethune X Intermediate CA $STAMP" \
    -out "$CA_TMP/intermediate_ca.csr" || exit 1
  printf 'basicConstraints=critical,CA:TRUE,pathlen:0\nkeyUsage=critical,keyCertSign,cRLSign\nsubjectKeyIdentifier=hash\nauthorityKeyIdentifier=keyid:always,issuer\n' \
    > "$CA_TMP/intermediate_ca.ext"
  openssl x509 -req -sha256 -days 1825 -in "$CA_TMP/intermediate_ca.csr" \
    -CA "$CA_TMP/root_ca.cert" -CAkey "$CA_TMP/root_ca.key" -CAcreateserial \
    -extfile "$CA_TMP/intermediate_ca.ext" -out "$CA_TMP/intermediate_ca.cert" || exit 1
  openssl verify -CAfile "$CA_TMP/root_ca.cert" "$CA_TMP/intermediate_ca.cert" || exit 1
  # init.sh 用 -CAkey 且不传口令，中间 CA 私钥必须无口令（genrsa 默认即无口令）
  CA_DIR="$CA_TMP"
fi

echo "==> 生成证书：$OUT（HOST_IP=$HOST_IP）"
if ! docker run --rm \
      -v "$CA_DIR:/ca:ro" \
      -v "$OUT:/output" \
      -e CERT_DIR=/output \
      -e HOST_IP="$HOST_IP" \
      -e JAVA_PATH=/opt/java/bin/java \
      --entrypoint /bin/bash \
      "$IMAGE" -c "bash /ca/init.sh"; then
  echo "ERROR: 证书生成失败" >&2
  exit 1
fi

[ -f "$OUT/logo.png" ] || { echo "ERROR: 生成后缺少 logo.png（CA 流程异常）" >&2; exit 1; }

# proxy 证书：用同一套 CA 再签一张（CN 不同）。放在同一次调用里做，
# 这样 per-install CA 的私钥不需要落盘复用。
if [ -n "$PROXY_OUT" ]; then
  [ -n "$PROXY_CN" ] || { echo "ERROR: 指定 --proxy-out 时必须给 --proxy-cn" >&2; exit 1; }
  mkdir -p "$PROXY_OUT"
  if [ ! -s "$PROXY_OUT/images/zcloud.png" ] || [ "$FORCE" = "true" ]; then
    echo "==> 生成 proxy 证书（CN=$PROXY_CN）"
    if ! docker run --rm \
          -v "$CA_DIR:/ca:ro" \
          -v "$PROXY_OUT:/output" \
          -e CERT_DIR=/output/images \
          -e HOST_IP="$HOST_IP" \
          -e SERVER_CN="$PROXY_CN" \
          -e JAVA_PATH=/opt/java/bin/java \
          --entrypoint /bin/bash \
          "$IMAGE" -c "bash /ca/init.sh"; then
      echo "ERROR: proxy 证书生成失败" >&2
      exit 1
    fi
    [ -s "$PROXY_OUT/images/zcloud.png" ] || { echo "ERROR: proxy 证书产物缺失" >&2; exit 1; }
  fi
fi
chown -R 1000:1000 "$OUT" 2>/dev/null || true
echo "证书生成完成"
