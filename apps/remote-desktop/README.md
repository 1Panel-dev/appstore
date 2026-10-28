## 产品介绍

**远程桌面** 是一套可自建的远程桌面服务：在 1Panel 上一键部署信令、Postgres 与 coturn（TURN 中继）。员工安装客户端后，用设备 ID + 密码连接；打洞失败时自动走公司 TURN。

## 主要功能

- **一键服务端**：信令 HTTP/WebSocket + Postgres + coturn
- **客户端主路径**：设备 ID + 密码；账号登录可选
- **下载页**：安装后生成中文下载页，链到公开 Releases
- **公开镜像**：`ghcr.io/jiayuexin/rd-server:<version>`，任意 1Panel 主机可直接拉取
