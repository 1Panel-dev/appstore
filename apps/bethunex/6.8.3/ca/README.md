# zCloud CA 证书管理

## 目录结构

```
config/ca/
├── root_ca.cert           # 根 CA 证书（预置，来自 AutoDeploy-Engine）
├── intermediate_ca.cert   # 中间 CA 证书（预置）
├── intermediate_ca.key    # 中间 CA 私钥（预置）
├── init.sh                # 证书初始化脚本
└── README.md              # 本文档
```

## 证书生成流程

与 AutoDeploy-Engine `__CreateCa` 函数一致，生成三层完整证书链：

```
server.cert → intermediate_ca → root_ca
```

### 使用方法

**单节点部署**：

```bash
# 1. 在目标服务器上运行证书初始化脚本
cd zCloud-Docker-Swarm/docker-swarm
bash config/ca/init.sh

# 脚本会在 ${HOME}/dbaas/soft-install/images/ 生成：
# - zcloud.png       加密的 PKCS12 证书
# - logo.png         加密的密码文件
# - server.p12       未加密的 PKCS12
# - server.cert      服务器证书
# - server.key       服务器私钥
# - 3fbf290e...png   根 CA 证书
# - 2c2b0e57...png   中间 CA 证书

# 2. 验证证书链完整性
openssl pkcs12 -in ~/dbaas/soft-install/images/server.p12 \
    -info -nokeys -passin pass:UssdV#$%CCdfd

# 应显示 3 个证书：server.cert + intermediate_ca + root_ca
```

**指定自定义路径和 IP**：

```bash
CERT_DIR=/opt/zcloud/certs HOST_IP=192.168.1.100 bash config/ca/init.sh
```

## 依赖证书的服务

| 服务 | 用途 | 证书路径 |
|-----|------|---------|
| dbaas-infrastructure | gRPC SSL 通信 | `/home/zcloud/dbaas/soft-install/images/zcloud.png` |
| zoramon-mgr | gRPC TLS 双向认证 | `/images/zcloud.png` |
| dbaas-database-snapshot | gRPC SSL 通信 | 同 dbaas-infrastructure |
| zdbmon-mgr | gRPC SSL 通信 | 同 dbaas-infrastructure |

## Docker 挂载示例

```yaml
services:
  dbaas-infrastructure:
    volumes:
      - ~/dbaas/soft-install/images:/home/zcloud/dbaas/soft-install/images:ro

  zoramon-mgr:
    volumes:
      - ~/dbaas/soft-install/images:/images:ro
```

## 多节点部署注意事项

**当前方案适用于单节点部署**。证书中的 SAN（Subject Alternative Name）包含宿主机 IP，在多节点 Swarm 环境中：

- 每个节点 IP 不同，共享证书会导致 TLS 验证失败
- 需要采用域名证书或动态证书生成方案

**长期方案**（待设计）：
1. 域名证书：使用 `*.zcloud.local` 替代 IP
2. Docker Secrets 管理：每个节点独立挂载证书
3. 动态证书生成：容器启动时根据环境变量生成

## 文件来源

预置的 CA 文件来自 AutoDeploy-Engine 项目：
```
AutoDeploy-Engine/config/ca/
├── root_ca.cert
├── intermediate_ca.cert
└── intermediate_ca.key
```

这些文件在 zCloud 产品构建时预置，用于统一管理所有服务的 TLS 证书。