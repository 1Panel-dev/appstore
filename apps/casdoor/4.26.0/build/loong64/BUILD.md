# casdoor 4.26.0 — loong64 构建配方

- 上游仓库: https://github.com/casbin/casdoor
- 版本/tag: `v4.26.0`
- 路线: go_fe（Go 后端 + 前端）
- 前端构建:
  ```
  npm ci --no-audit --no-fund || npm install --no-audit --no-fund
  ```
  ```
  npm run build
  ```
- Go 构建: `go build -o casdoor .`
- 构建前准备（如依赖补丁）:
  ```
  python -c "import json;p=json.load(open('web/package.json'));p.get('scripts',{}).pop('preinstall',None);json.dump(p,open('web/package.json','w'))"
  ```
  ```
  cat > entrypoint.sh <<'EPOTE'
#!/bin/sh
if [ -f /conf/app.conf ]; then
  CONF=/conf/app.conf; ARGS=-config=/conf/app.conf
else
  CONF=/app/conf/app.conf; ARGS=
fi
if [ -f "$CONF" ]; then
  [ -n "$driv
  ```
  ```
  GOPROXY=https://goproxy.cn,direct "E:/05AIProjects/30zcode/08crosscompile/02loongson2k3000/1panel_port/toolchain/go/bin/go.exe" get modernc.org/sqlite@v1.34.5 && GOPROXY=https://goproxy.cn,direct "E:/
  ```

## 构建底座说明

- 所有镜像底座来自 `lcr.loongnix.cn`（龙芯社区 registry，loong64 manifest）
- 交叉编译：可在 x86 PC 上用 `GOARCH=loong64` 交叉编译纯 Go 二进制，再到龙芯设备 docker build 组装镜像
- 或直接在龙芯设备上原生构建（龙芯 2K3000 8 核实测单应用几分钟）

## 镜像组装 Dockerfile

```dockerfile
# loong64 self-built (wave7) - store-compatible entrypoint (env -> conf/app.conf)
FROM lcr.loongnix.cn/library/alpine:3.24
RUN apk add --no-cache ca-certificates tzdata 2>/dev/null || (sed -i 's|dl-cdn.alpinelinux.org|mirrors.tuna.tsinghua.edu.cn|g' /etc/apk/repositories && apk add --no-cache ca-certificates tzdata)
WORKDIR /app
COPY casdoor /app/casdoor
COPY conf /app/conf
COPY web/build /app/web/build
COPY entrypoint.sh /app/entrypoint.sh
RUN chmod +x /app/casdoor /app/entrypoint.sh
ENV TZ=Asia/Shanghai
EXPOSE 8000
ENTRYPOINT ["/bin/sh", "/app/entrypoint.sh"]
```

## 烟囱测试

- 类型: http，等待 12s，HTTP GET /api/health
