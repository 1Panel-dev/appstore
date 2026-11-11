# prometheus 3.16.0 — loong64 构建配方

- 上游仓库: https://github.com/prometheus/prometheus
- 版本/tag: `v3.16.0`
- 路线: go_fe（Go 后端 + 前端）
- 前端构建:
  ```
  pnpm install --frozen-lockfile || pnpm install
  ```
  ```
  pnpm run build:mantine-ui
  ```
- Go 构建: `go build -o prometheus ./cmd/prometheus`

## 构建底座说明

- 所有镜像底座来自 `lcr.loongnix.cn`（龙芯社区 registry，loong64 manifest）
- 交叉编译：可在 x86 PC 上用 `GOARCH=loong64` 交叉编译纯 Go 二进制，再到龙芯设备 docker build 组装镜像
- 或直接在龙芯设备上原生构建（龙芯 2K3000 8 核实测单应用几分钟）

## 镜像组装 Dockerfile

```dockerfile
# loong64 self-built (wave3)
FROM lcr.loongnix.cn/library/alpine:3.24
RUN apk add --no-cache ca-certificates tzdata 2>/dev/null || (sed -i 's|dl-cdn.alpinelinux.org|mirrors.tuna.tsinghua.edu.cn|g' /etc/apk/repositories && apk add --no-cache ca-certificates tzdata)
WORKDIR /app
COPY prometheus /app/prometheus
COPY prometheus.yml /etc/prometheus/prometheus.yml
RUN chmod +x /app/prometheus && mkdir -p /prometheus
ENV TZ=Asia/Shanghai
EXPOSE 9090
ENTRYPOINT ["/app/prometheus"]
CMD ["--config.file=/etc/prometheus/prometheus.yml", "--storage.tsdb.path=/prometheus"]
```

## 烟囱测试

- 类型: http，等待 12s，HTTP GET /status
