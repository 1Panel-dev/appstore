# Fizzy

Fizzy 是 37signals 推出的 Kanban 项目管理工具，用于组织议题、想法和团队工作。
该应用包使用 Fizzy 官方 Docker 镜像，并将数据库、队列、缓存和附件统一保存在
安装实例的 `data` 目录中。

## 安装和首次登录

1. 选择一个未占用的 HTTP 端口。
2. 将 `BASE_URL` 设置为浏览器实际访问 Fizzy 的完整地址。
3. `SECRET_KEY_BASE` 由 1Panel 自动生成，安装后不要修改。
4. SMTP 可以留空。没有配置 SMTP 时，登录验证码会写入 Fizzy 容器日志，可在
   1Panel 的容器日志页面查看。

## SMTP

Fizzy 自身后台不提供 SMTP 服务器配置页面。需要发邮件时，请在 1Panel 的 Fizzy
应用参数中填写发件地址、SMTP 服务器、端口、用户名、密码和 TLS 选项，然后重启
应用。

`SMTP_TLS=true` 仅用于要求隐式 TLS 的 SMTPS 服务器，通常使用端口 `465`。
大多数使用 STARTTLS 的服务器应保持 `SMTP_TLS=false`，通常使用端口 `587`。

## 域名和 HTTPS

Fizzy 容器内部只提供 HTTP。请使用 1Panel 网站功能创建反向代理并配置 HTTPS
证书。域名启用后，将 `BASE_URL` 修改为最终地址，例如
`https://fizzy.example.com`，然后重启应用。

## 数据和备份

所有持久化内容都位于安装实例的 `data` 目录，对应容器内的
`/rails/storage`。备份前建议停止应用，并完整备份该目录。恢复时应恢复完整目录
并保留 UID/GID `1000:1000` 的写权限。

## 版本说明

该应用使用 `ghcr.io/basecamp/fizzy:latest` 滚动镜像。上游更新可能在重新拉取
镜像后直接生效，因此升级前必须备份 `data` 目录。

## 许可证

Fizzy 的源代码采用 O’Saasy License。该许可证允许自托管、修改和分发，但限制将
Fizzy 作为竞争性的托管或 SaaS 服务提供。它不是 OSI 批准的标准开源许可证；
部署前请阅读上游 `LICENSE.md`。
