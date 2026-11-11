# caddy 2.11.9 — loong64 构建配方

- 上游仓库: https://github.com/caddyserver/caddy
- 版本/tag: `v2.11.9`（纯 Go，标准 go build；如需插件用 xcaddy）
- 路线: go_only

## 构建底座说明

- 所有镜像底座来自 `lcr.loongnix.cn`（龙芯社区 registry，loong64 manifest）
- 交叉编译：可在 x86 PC 上用 `GOARCH=loong64` 交叉编译纯 Go 二进制，再到龙芯设备 docker build 组装镜像
- 或直接在龙芯设备上原生构建（龙芯 2K3000 8 核实测单应用几分钟）

## 镜像组装 Dockerfile

```dockerfile
# loong64 — replicates official caddy alpine image layout
FROM lcr.loongnix.cn/library/alpine:3.24
RUN apk add --no-cache ca-certificates tzdata 2>/dev/null || \
    (sed -i 's|dl-cdn.alpinelinux.org|mirrors.tuna.tsinghua.edu.cn|g' /etc/apk/repositories && apk add --no-cache ca-certificates tzdata)
COPY caddy /usr/bin/caddy
COPY Caddyfile /etc/caddy/Caddyfile
COPY index.html /usr/share/caddy/index.html
RUN chmod a+x /usr/bin/caddy
ENV XDG_CONFIG_HOME=/config
ENV XDG_DATA_HOME=/data
WORKDIR /srv
VOLUME /data /config
EXPOSE 80 443 2019
ENTRYPOINT ["caddy"]
CMD ["run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]
```

## 烟囱测试

- `caddy version` 输出版本号；`caddy run` 后 2019 端口健康检查
