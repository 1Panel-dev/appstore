# keycloak 26.8.2 — loong64 构建配方

- 上游仓库: (release artifact)
- 版本/tag: `26.8.2`
- 路线: artifact（官方 release 资产直发）

## 构建底座说明

- 所有镜像底座来自 `lcr.loongnix.cn`（龙芯社区 registry，loong64 manifest）
- 交叉编译：可在 x86 PC 上用 `GOARCH=loong64` 交叉编译纯 Go 二进制，再到龙芯设备 docker build 组装镜像
- 或直接在龙芯设备上原生构建（龙芯 2K3000 8 核实测单应用几分钟）

## 镜像组装 Dockerfile

```dockerfile
FROM lcr.loongnix.cn/library/eclipse-temurin:21-jdk
RUN apt-get update && apt-get install -y --no-install-recommends unzip && rm -rf /var/lib/apt/lists/*
COPY keycloak /opt/keycloak
RUN chmod -R +x /opt/keycloak/bin/*.sh
WORKDIR /opt/keycloak
EXPOSE 8080
ENTRYPOINT ["/opt/keycloak/bin/kc.sh"]
CMD ["start-dev"]
```

## 烟囱测试

- 类型: http，等待 45s，HTTP GET /
