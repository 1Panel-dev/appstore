## 使用说明

> 默认 Web 访问端口为 **80**，容器启动后等待约 **60 秒**再访问，等待内置的数据库和应用服务完全就绪。
>
> **`前端访问地址（FRONTEND_URL）`** 为可选配置：若通过 IP 直接访问可留空；若需要通过域名或 HTTPS 访问，请填入完整地址（如 `https://your-domain.com`），该配置影响 CORS 和 OAuth2 回调等功能。
>
> 数据持久化目录：
> - `./mysql` → 容器内 `/var/lib/mysql`（数据库文件）
> - `./storage` → 容器内 `/app/storage`（配置、证书、日志、上传等）

## 产品介绍

**OneClickVirt** 是一个可扩展的通用虚拟化管理平台，提供 Docker 一体化镜像（自带前端 Nginx、后端 Go 服务与内置数据库），无需额外配置外部数据库即可直接部署运行。

- **amd64** 架构内置 MySQL 8.0
- **arm64** 架构内置 MariaDB

详细文档见 [www.spiritlhl.net](https://www.spiritlhl.net/guide/oneclickvirt/oneclickvirt_precheck.html)

## 支持的虚拟化平台

| 类型标识 | 平台 | 实例类型 |
|---------|------|----------|
| `lxd` | LXD | 容器、虚拟机 |
| `incus` | Incus | 容器、虚拟机 |
| `docker` | Docker | 容器 |
| `podman` | Podman | 容器 |
| `containerd` | Containerd (nerdctl) | 容器 |
| `proxmox` | Proxmox VE | 容器、虚拟机 |
| `qemu` | QEMU | 虚拟机 |
| `kubevirt` | KubeVirt | 虚拟机 |

## 主要功能

- **一体化部署**：前端（Nginx）、后端（Go）与数据库全部打包在单一镜像中，开箱即用
- **多后端支持**：支持 LXD、Incus、Docker、Podman、Containerd、Proxmox VE、QEMU、KubeVirt 等虚拟化后端
- **Web 管理界面**：直观的图形化界面，方便创建、配置和管理虚拟机/容器实例
- **多架构支持**：同时提供 `amd64` 与 `arm64` 架构镜像，自动适配
- **数据持久化**：数据库与应用存储均挂载至宿主机目录，重启不丢失数据
- **灵活访问配置**：支持通过 `FRONTEND_URL` 环境变量配置域名与 HTTPS 访问
