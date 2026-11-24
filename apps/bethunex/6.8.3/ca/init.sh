#!/bin/bash
#
# zCloud 证书初始化脚本（完整版）
#
# 功能：
#   1. 使用预置 CA 文件生成三层证书链（server → intermediate → root）
#   2. 导出 PKCS12 格式
#   3. 使用 AES-GCM 加密证书文件
#
# 与 AutoDeploy-Engine __CreateCa 流程完全一致
#
# 使用方法（推荐通过 Docker 容器执行，无需宿主机 Java）：
#   docker run --rm \
#       -v $(pwd):/ca:ro \
#       -v /data/shared/dbaas/soft-install:/output \
#       --entrypoint /bin/bash \
#       zcloud-java-base:17.0.11-9-amd64 \
#       -c "CERT_DIR=/output/images JAVA_PATH=/opt/java/bin/java bash /ca/init.sh"
#
# 部署时由 03-storage.sh 自动调用，用户无需手动执行此脚本。
#

set -e
set -u

# ============================================
# 配置变量
# ============================================

# 证书目录（可覆盖）
CERT_DIR=${CERT_DIR:-"/data/shared/dbaas/soft-install/images"}

# CA 文件目录（脚本所在目录）
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CA_DIR="${SCRIPT_DIR}"

# 宿主机 IP（可覆盖）
HOST_IP=${HOST_IP:-""}

# Java 路径（用于加密）
JAVA_PATH=${JAVA_PATH:-"/opt/jdk-17.0.11+9/bin/java"}

# 证书配置（与 AutoDeploy-Engine 一致）
CA_PASSWORD="UssdV#\$%CCdfd"
# CN 允许环境变量覆盖（默认 eauBi 给 zCloud 主证书；ensure_proxy_images 传 SERVER_CN=Pmuz9… 生成 proxy 专用证书）
SERVER_CN="${SERVER_CN:-eauBiGEuXG8LaDo7GTKq}"

# 附加 SAN IP 列表（空格/逗号分隔；多节点下由 03-storage.sh 传全部节点 IP）
#   证书 SAN 原本只有 ${host_ip} 与 127.0.0.1（单节点足够）。多节点下服务可能被调度到任意节点，
#   客户端用该节点 IP 回连时 SAN 不匹配 → 证书校验失败；补齐全节点 IP 后一份证书通吃。
#   默认空 = 单节点/代理证书路径（ensure_proxy_images 不传此变量）行为逐字不变。
EXTRA_SAN_IPS="${EXTRA_SAN_IPS:-}"

# ============================================
# 颜色输出
# ============================================

# 颜色: 仅终端 TTY 且未设置 NO_COLOR 时启用
if [ -t 1 ] && [ -z "${NO_COLOR:-}" ]; then
    RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
else
    RED=''; GREEN=''; YELLOW=''; BLUE=''; NC=''
fi

function log_info() {
    echo "[INFO] $1"
}

function log_warn() {
    echo "[WARN] $1"
}

function log_error() {
    echo "[ERROR] $1"
}

function log_step() {
    echo "[STEP] $1"
}

# ============================================
# 获取宿主机 IP
# ============================================

function get_host_ip() {
    local ip="${HOST_IP}"

    if [[ -n "${ip}" ]]; then
        echo "${ip}"
        return
    fi

    # 从网络接口获取
    ip=$(ip addr show 2>/dev/null | grep "inet " | grep -v "127.0.0.1" | head -1 | awk '{print $2}' | cut -d/ -f1)

    if [[ -z "${ip}" ]]; then
        ip=$(hostname -I 2>/dev/null | awk '{print $1}')
    fi

    echo "${ip}"
}

# ============================================
# 检查 Java 环境
# ============================================

function check_java() {
    if [[ -f "${JAVA_PATH}" ]]; then
        log_info "Java 环境: ${JAVA_PATH}"
        return 0
    fi

    # 尝试其他常见路径
    for path in /usr/bin/java /usr/local/bin/java /opt/java/bin/java; do
        if [[ -f "${path}" ]]; then
            JAVA_PATH="${path}"
            log_info "Java 环境: ${JAVA_PATH}"
            return 0
        fi
    done

    log_error "未找到 Java 环境，无法加密证书文件"
    log_info "请通过 Docker 容器运行此脚本（无需宿主机 Java）："
    log_info "  docker run --rm -v \$(pwd):/ca:ro -v /data/shared/dbaas/soft-install:/output \\"
    log_info "    --entrypoint /bin/bash zcloud-java-base:xxx \\"
    log_info "    -c \"CERT_DIR=/output/images JAVA_PATH=/opt/java/bin/java bash /ca/init.sh\""
    return 1
}

# ============================================
# AES-GCM 加密函数
# ============================================

function encrypt_file() {
    local input_file="$1"
    local output_file="$2"

    if [[ ! -f "${input_file}" ]]; then
        log_error "输入文件不存在: ${input_file}"
        return 1
    fi

    cd "${CERT_DIR}"
    "${JAVA_PATH}" -classpath "${CA_DIR}" AesGcmNewUtil encryptByte "${input_file}" "${output_file}"

    if [[ -f "${output_file}" ]]; then
        log_info "加密成功: ${output_file} ($(stat -c%s "${output_file}" 2>/dev/null || stat -f%z "${output_file}") bytes)"
        return 0
    else
        log_error "加密失败: ${output_file}"
        return 1
    fi
}

function encrypt_string() {
    local input_string="$1"
    local output_file="$2"

    cd "${CERT_DIR}"
    "${JAVA_PATH}" -classpath "${CA_DIR}" AesGcmNewUtil encrypt "${input_string}" "${output_file}"

    if [[ -f "${output_file}" ]]; then
        log_info "加密成功: ${output_file}"
        return 0
    else
        log_error "加密失败: ${output_file}"
        return 1
    fi
}

# ============================================
# 证书生成函数（与 AutoDeploy-Engine __CreateCa 一致）
# ============================================

function generate_certificates() {
    local target_dir="$1"
    local host_ip="$2"

    log_step "=== 开始生成证书 ==="
    log_info "目标目录: ${target_dir}"
    log_info "宿主机 IP: ${host_ip}"

    # Step 1: 检查 CA 文件
    log_step "[1/7] 检查 CA 文件..."
    for file in root_ca.cert intermediate_ca.cert intermediate_ca.key; do
        if [[ ! -f "${CA_DIR}/${file}" ]]; then
            log_error "CA 文件不存在: ${CA_DIR}/${file}"
            exit 1
        fi
    done
    log_info "CA 文件检查通过"

    # Step 2: 创建目录结构
    log_step "[2/7] 创建目录结构..."
    mkdir -p "${target_dir}"
    mkdir -p "${target_dir}/login"
    > "${target_dir}/index.txt"
    echo "1000" > "${target_dir}/serial"

    # Step 3: 复制 CA 文件
    log_step "[3/7] 复制 CA 文件..."
    \cp -f "${CA_DIR}/root_ca.cert" "${target_dir}/"
    \cp -f "${CA_DIR}/intermediate_ca.cert" "${target_dir}/"
    \cp -f "${CA_DIR}/intermediate_ca.key" "${target_dir}/"

    # Step 4: 生成证书配置
    log_step "[4/7] 生成证书配置..."
    # 附加 SAN（多节点）：IP.3 起顺延，去重且排除 host_ip / 127.0.0.1（避免与 IP.1/IP.2 重复 ——
    #   openssl 对重复 SAN 不报错但会生成冗余条目）。只收形如 IPv4/IPv6 的 token，域名留给
    #   ${SERVER_CN}；解析不出的 token 直接丢弃（宁少一条 SAN，不生成语法非法的 cnf 让 openssl 失败）
    local _san_extra="" _ip _n=2
    for _ip in ${EXTRA_SAN_IPS//,/ }; do
        [ -n "$_ip" ] || continue
        [ "$_ip" = "${host_ip}" ] && continue
        [ "$_ip" = "127.0.0.1" ] && continue
        case "$_ip" in
            *[!0-9a-fA-F:.]*) log_warn "EXTRA_SAN_IPS 跳过非 IP 项: ${_ip}"; continue ;;
        esac
        _n=$((_n + 1))
        _san_extra="${_san_extra}IP.${_n} = ${_ip}
"
    done
    cat > "${target_dir}/server_csr.cnf" <<EOF
[ req ]
default_bits        = 2048
default_md          = sha256
prompt              = no
encrypt_key         = no
distinguished_name  = req_distinguished_name
req_extensions      = req_extensions_ext

[ req_distinguished_name ]
countryName                 = CN
stateOrProvinceName         = Sichuan
localityName                = Chengdu
organizationName            = Enmo zCloud
organizationalUnitName      = Enmo zCloud
commonName                  = ${SERVER_CN}
emailAddress                = zcloud@enmotech.com

[ req_extensions_ext ]
subjectKeyIdentifier   = hash
basicConstraints       = CA:FALSE
keyUsage               = critical,nonRepudiation,digitalSignature,keyEncipherment,dataEncipherment
extendedKeyUsage       = serverAuth,clientAuth
subjectAltName         = @alt_names

[alt_names]
IP.1 = ${host_ip}
IP.2 = 127.0.0.1
EOF
    # ★ 附加 SAN 必须在 EOF **之后**追加，不能写成 `${_san_extra}EOF`：
    #   那样这一行不再是裸的定界符，heredoc 永不结束 → bash 报「文档在第 N 行被文件结束符分隔」
    if [ -n "${_san_extra}" ]; then
        printf '%s' "${_san_extra}" >> "${target_dir}/server_csr.cnf"
        log_info "附加 SAN 节点 IP: $(printf '%s' "${_san_extra}" | tr '\n' ' ')"
    fi

    # Step 5: 生成私钥和 CSR，使用 CA 签名
    log_step "[5/7] 生成服务器证书（CA 签名）..."
    openssl genrsa -out "${target_dir}/server.key" 2048 2>/dev/null
    openssl req -new -key "${target_dir}/server.key" -out "${target_dir}/server.csr" -config "${target_dir}/server_csr.cnf"
    openssl x509 -req -days 1825 -sha256 \
        -extfile "${target_dir}/server_csr.cnf" \
        -extensions req_extensions_ext \
        -in "${target_dir}/server.csr" \
        -CA "${target_dir}/intermediate_ca.cert" \
        -CAkey "${target_dir}/intermediate_ca.key" \
        -CAcreateserial \
        -out "${target_dir}/server.cert"

    # Step 6: 合成证书链并导出 PKCS12
    log_step "[6/7] 合成完整证书链（三层）..."
    cat "${target_dir}/server.cert" "${target_dir}/intermediate_ca.cert" "${target_dir}/root_ca.cert" > "${target_dir}/server-chain.crt"

    openssl pkcs12 -export -name "tomcat" \
        -in "${target_dir}/server-chain.crt" \
        -inkey "${target_dir}/server.key" \
        -password pass:${CA_PASSWORD} \
        -out "${target_dir}/server.p12"

    # 验证证书链
    local cert_count=$(openssl pkcs12 -in "${target_dir}/server.p12" -info -nokeys -passin pass:${CA_PASSWORD} 2>/dev/null | grep -c "subject=" || echo "0")
    log_info "证书链包含 ${cert_count} 个证书（应为 3 个）"

    # Step 7: 加密所有文件
    log_step "[7/7] 加密证书文件..."

    # 根 CA 证书：直接重命名（不加密，按 AutoDeploy-Engine 规范）
    mv "${target_dir}/root_ca.cert" "${target_dir}/3fbf290e-4ac0-4d69-b0b3-a4f2865b9ba3.png"
    log_info "根 CA: 3fbf290e-4ac0-4d69-b0b3-a4f2865b9ba3.png ($(stat -c%s "${target_dir}/3fbf290e-4ac0-4d69-b0b3-a4f2865b9ba3.png") bytes)"

    # 中间 CA 证书：加密
    encrypt_file "${target_dir}/intermediate_ca.cert" "${target_dir}/2c2b0e57-79ef-4eb4-bcf2-36761b5efccf.png"

    # 中间 CA 私钥：加密
    encrypt_file "${target_dir}/intermediate_ca.key" "${target_dir}/d4189ce8-ea22-4724-b59b-0bdb463ee8ef.png"

    # server.p12：加密为 zcloud.png
    encrypt_file "${target_dir}/server.p12" "${target_dir}/zcloud.png"

    # 密码：加密为 logo.png
    encrypt_string "${CA_PASSWORD}" "${target_dir}/logo.png"

    # 清理中间文件
    rm -f "${target_dir}/server.csr" "${target_dir}/server-chain.crt"
    rm -f "${target_dir}/intermediate_ca.cert" "${target_dir}/intermediate_ca.key"
    rm -f "${target_dir}/intermediate_ca.srl"

    # 设置权限
    # ★★ 这里的容错是**承重**的，不是防御性收尾（2026-10-14 85.3 真机驱动）：
    #    本脚本第 23-24 行有 `set -e`/`set -u` ⇒ 这条 chmod 一旦返回非 0 会**立刻终止整个
    #    init.sh**，连紧随其后的 `=== 证书生成完成 ===` 都不打印，调用方（20-cert-init）随之
    #    失败 —— 而**证书本身早已全部生成成功**，失败只发生在收口动作上（现场 pod 日志末尾
    #    只有一行 `chmod: changing permissions of '/data/zcloud/proxy/images': Operation not
    #    permitted`，其后什么都没有）。
    #    而 k8s/pvc 通道下它**必然**返回非 0：target_dir 是 `proxy-images` PVC 的**挂载点根**，
    #    由 provisioner 以 root 创建（perm 来自 SC，k3s local-path = 0777），而 20-cert-init 在
    #    该通道下已去 root（`zcloud.hooksUseRoot`）以 uid 1000 运行 ⇒ 对**非自己所有**的目录层
    #    chmod 必 EPERM（**子项**因由本进程产出而成功 —— 现场即「证书全生成好了、目录里文件
    #    全是 1000:1000 755，只有挂载点根 root root 改不动」）。★ 该失败**不改变证书可用性**：
    #    内容已生成并加密、子项已 755、目录层由 SC 保证可进入 ⇒ 不该有阻断力，只降为告警。
    #    swarm/hostPath 通道下以 root 运行、chmod 恒成功 ⇒ 此行行为**逐字不变**。
    #    ★ 不写 `2>/dev/null`：只去掉它的阻断力，不去掉可观测性（真失败时错误照常可见）。
    chmod -R 755 "${target_dir}" || log_warn "chmod -R 755 ${target_dir} 未成功（目录层归 SC/provisioner 所有，pvc/非 root 通道下属预期）；证书已生成，继续"

    log_step "=== 证书生成完成 ==="
}

# ============================================
# 主逻辑
# ============================================

function main() {
    echo ""
    echo "=========================================="
    echo "    zCloud 证书初始化脚本"
    echo "    （完整流程：生成 + 加密）"
    echo "=========================================="
    echo ""

    # 检查 Java 环境
    if ! check_java; then
        exit 1
    fi

    # 获取宿主机 IP
    HOST_IP=$(get_host_ip)
    if [[ -z "${HOST_IP}" ]]; then
        log_error "无法获取宿主机 IP"
        log_info "请指定: HOST_IP=192.168.1.100（通过环境变量传入）"
        exit 1
    fi

    log_info "宿主机 IP: ${HOST_IP}"
    log_info "证书目录: ${CERT_DIR}"
    log_info "CA 文件目录: ${CA_DIR}"
    echo ""

    # 检查是否需要重新生成
    if [[ -f "${CERT_DIR}/server.p12" ]]; then
        local cert_count=$(openssl pkcs12 -in "${CERT_DIR}/server.p12" -info -nokeys -passin pass:${CA_PASSWORD} 2>/dev/null | grep -c "subject=" || echo "0")

        if [[ "${cert_count}" -ge 3 ]]; then
            log_info "证书链已完整（${cert_count} 个证书），无需重新生成"
        else
            log_warn "证书链不完整（仅 ${cert_count} 个证书），需要重新生成"
            rm -rf "${CERT_DIR}"/*.p12 "${CERT_DIR}"/*.png "${CERT_DIR}"/server.* "${CERT_DIR}"/intermediate_ca.*
            generate_certificates "${CERT_DIR}" "${HOST_IP}"
        fi
    else
        generate_certificates "${CERT_DIR}" "${HOST_IP}"
    fi

    # 最终验证
    echo ""
    log_info "=== 验证证书文件 ==="

    local files_ok=true
    local expected_sizes=(
        "zcloud.png:>7000"
        "logo.png:>30"
        "server.p12:>7000"
        "server.key:>1600"
        "server.cert:>1700"
        "3fbf290e-4ac0-4d69-b0b3-a4f2865b9ba3.png:>3200"
        "2c2b0e57-79ef-4eb4-bcf2-36761b5efccf.png:>2000"
        "d4189ce8-ea22-4724-b59b-0bdb463ee8ef.png:>3000"
    )

    for item in "${expected_sizes[@]}"; do
        local file="${item%%:*}"
        local min_size="${item##*:}"

        if [[ -f "${CERT_DIR}/${file}" ]]; then
            local size=$(stat -c%s "${CERT_DIR}/${file}" 2>/dev/null || stat -f%z "${CERT_DIR}/${file}")
            min_size="${min_size#>}"
            if [[ -n "${size}" && "${size}" -gt "${min_size}" ]]; then
                log_info "✓ ${file} (${size} bytes)"
            else
                log_warn "⚠ ${file} (${size} bytes, 可能不完整)"
                files_ok=false
            fi
        else
            log_error "✗ ${file} 不存在"
            files_ok=false
        fi
    done

    if [[ "${files_ok}" == true ]]; then
        echo ""
        echo "=========================================="
        log_info "证书初始化完成！"
        echo "=========================================="
        echo ""
        log_info "证书挂载示例："
        echo "  docker run -v ${CERT_DIR}:/images ..."
        echo ""
        log_info "验证证书链："
        echo "  openssl pkcs12 -in ${CERT_DIR}/server.p12 -info -nokeys -passin pass:${CA_PASSWORD}"
    else
        log_error "证书验证失败"
        exit 1
    fi
}

main "$@"
