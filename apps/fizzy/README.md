# Fizzy

Fizzy 是 37signals 推出的 Kanban 项目管理工具，用于组织议题、想法和团队工作。
该应用包使用 Fizzy 官方 Docker 镜像，并将数据库、队列、缓存和附件统一保存在
安装实例的 `data` 目录中。

## 安装和首次登录

1. 选择一个未占用的 HTTP 端口。
2. 将 `BASE_URL` 设置为浏览器实际访问 Fizzy 的完整地址。
3. `SECRET_KEY_BASE` 由 1Panel 自动生成，安装后不要修改。
4. 必须填写可用的 SMTP 配置。Fizzy 的注册和登录通过电子邮件验证码完成，只有
   邮件投递正常后用户才能完成登录。

## SMTP

Fizzy 自身后台不提供 SMTP 服务器配置页面。请在 1Panel 的 Fizzy 应用参数中填写
发件地址、SMTP 服务器和端口；用户名、密码和 TLS 选项按 SMTP 服务商要求填写。
这些 SMTP 设置以后仍可在 1Panel 中编辑，但每次修改后都必须重启应用；在邮件投递
恢复正常前，用户无法完成邮件验证码登录。

`SMTP_TLS=true` 仅用于要求隐式 TLS 的 SMTPS 服务器，通常使用端口 `465`。
大多数使用 STARTTLS 的服务器应保持 `SMTP_TLS=false`，通常使用端口 `587`。

## 域名和 HTTPS

直接通过 HTTP 端口访问时，请选择 HTTP 模式（`DISABLE_SSL=true`），并将
`BASE_URL` 设置为 `http://` 地址。

Fizzy 容器内部只提供 HTTP。若在 1Panel 网站中创建 HTTPS 反向代理并配置证书，
请在应用参数中选择 HTTPS 模式（`DISABLE_SSL=false`），将 `BASE_URL` 改为最终的
`https://` 地址（例如 `https://fizzy.example.com`），确认反向代理发送
`X-Forwarded-Proto`，然后重启应用。HTTPS 模式会启用 Rails 安全 Cookie、HSTS 和
HTTP 到 HTTPS 的重定向，因此容器的原始 HTTP 端口不再是正常访问路径。

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
