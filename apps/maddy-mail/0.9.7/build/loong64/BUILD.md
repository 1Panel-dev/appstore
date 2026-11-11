# maddy-mail 0.9.7 — loong64 构建配方

- 上游仓库: https://github.com/foxcpp/maddy
- 版本/tag: `0.9.7`
- git tag: `v0.9.7`（上游 tag 命名与版本号不一致时以此为准）
- 路线: go_only（纯 Go 构建）
- Go 构建: `go build -o maddy ./cmd/maddy`

## 构建底座说明

- 所有镜像底座来自 `lcr.loongnix.cn`（龙芯社区 registry，loong64 manifest）
- 交叉编译：可在 x86 PC 上用 `GOARCH=loong64` 交叉编译纯 Go 二进制，再到龙芯设备 docker build 组装镜像
- 或直接在龙芯设备上原生构建（龙芯 2K3000 8 核实测单应用几分钟）

## 镜像组装 Dockerfile

```dockerfile
```

## 烟囱测试

- 类型: cmd，等待 3s，命令 `--version`
