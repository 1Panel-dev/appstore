# sub2api 0.2.20 — loong64 构建配方

- 上游仓库: https://github.com/Wei-Shaw/sub2api
- 版本/tag: `0.2.20`
- git tag: `v0.2.20`（上游 tag 命名与版本号不一致时以此为准）
- 路线: go_fe（Go 后端 + 前端）
- 前端构建:
  ```
  pnpm install --frozen-lockfile || pnpm install
  ```
  ```
  pnpm run build
  ```
- Go 构建: `go build -o sub2api ./cmd/server`；ldflags: `-s -w -X main.Version=0.2.20 -X main.Commit=v0.2.20 -X main.BuildType=release`；tags: `embed`；cwd: `backend`

## 构建底座说明

- 所有镜像底座来自 `lcr.loongnix.cn`（龙芯社区 registry，loong64 manifest）
- 交叉编译：可在 x86 PC 上用 `GOARCH=loong64` 交叉编译纯 Go 二进制，再到龙芯设备 docker build 组装镜像
- 或直接在龙芯设备上原生构建（龙芯 2K3000 8 核实测单应用几分钟）

## 镜像组装 Dockerfile

```dockerfile
# loong64 self-built (wave27) - embed release build per upstream Dockerfile
FROM lcr.loongnix.cn/library/alpine:3.24
RUN apk add --no-cache ca-certificates tzdata libpq 2>/dev/null || (sed -i 's|dl-cdn.alpinelinux.org|mirrors.tuna.tsinghua.edu.cn|g' /etc/apk/repositories && apk add --no-cache ca-certificates tzdata libpq)
WORKDIR /app
COPY sub2api /app/sub2api
COPY resources /app/resources
RUN chmod +x /app/sub2api && mkdir -p /app/data
ENV TZ=Asia/Shanghai
EXPOSE 8080
ENTRYPOINT ["/app/sub2api"]
```

## 烟囱测试

- 类型: cmd，等待 3s，命令 `-version`
