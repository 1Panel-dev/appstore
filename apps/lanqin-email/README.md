# LanQin Email

## 产品介绍

LanQin Email 是采用 MIT 协议的自建邮件服务，集成 Webmail、管理后台、Go API、Nginx、Postfix、Dovecot 和 Rspamd，支持多域名、多邮箱、权限管理、附件、定时发送及 DKIM 签名。

本应用包使用项目维护者发布的 `ghcr.io/lanqin996/lanqin-email:1.1.6`，支持 amd64 和 arm64，默认使用 SQLite，无需另装数据库。

## 安装前准备

1. 准备解析到服务器的邮件主机名，例如 `mail.example.com`，并确认服务器允许 TCP 25 入站和出站。公网邮件投递应保留宿主机 25 端口。
2. 申请覆盖邮件主机名的有效 TLS 证书。在宿主机准备专用目录，内含 `fullchain.pem` 和 `privkey.pem` 两个真实文件，不要使用指向目录外的符号链接。仅向可信管理员开放私钥读取权限。安装表单填写该目录的绝对路径。
3. 证书目录只读挂载到 `/etc/lanqin/tls`。目录不存在时安装会失败；证书文件缺失时上游镜像可能降级为自签证书并禁用 SMTP 465/587，因此必须在安装前确认文件有效、匹配且可读。
4. 准备独立的强管理员密码和至少 32 位高熵随机字符的加密主密钥。可在服务器使用 `openssl rand -hex 32` 生成主密钥。不要将密码、私钥或主密钥提交到 Git。

## 安装参数

- 邮件服务器域名：仅填写主机名，不包含协议或路径。
- Webmail HTTPS 地址：填写最终 HTTPS 访问地址，例如 `https://mail.example.com`。
- 初始管理员邮箱及密码：仅用于首次初始化；以后请在应用内修改密码，不要通过修改安装参数重置账号。
- 加密主密钥：用于 Telegram Token 和 TOTP 种子加密，务必备份且不要随意更换。
- 宿主机证书目录：包含上述两个 PEM 文件的专用目录。
- Web HTTP 端口：默认 `8088`，避免占用 OpenResty 的 80/443。
- 邮件端口：默认 SMTP 25、SMTP TLS 465、SMTP STARTTLS 587、IMAP TLS 993、POP3 TLS 995。修改客户端端口后须同步调整客户端配置；公网 SMTP 25 不应随意变更。

## HTTPS 与反向代理

容器内 Web 仅监听 HTTP 80，不提供 Web HTTPS 443。本应用保持安全 Cookie 设置，必须通过 HTTPS 域名登录，不要直接通过 HTTP IP 地址使用。

在 1Panel 创建反向代理网站并启用 HTTPS，转发到本应用容器的 HTTP 80，或转发到宿主机的应用 Web 端口。若 OpenResty 在容器内运行，`127.0.0.1` 指向 OpenResty 容器自身，不能作为宿主机地址；可在共享 `1panel-network` 中使用应用实际容器名和端口 80。

默认可信代理层数为 1，与上游内置 Nginx 一致。额外加入 OpenResty 后，若需要真实客户端 IP，应在确认只有可信代理能到达 Web 入口、转发头由代理正确覆盖或追加后，将 Compose 中 `LANQIN_TRUSTED_PROXY_COUNT` 调整为实际可信层数（直接 OpenResty → 内置 Nginx → API 通常为 2）。不要在入口仍可被不可信客户端直连时盲目增加该值。

网站 HTTPS 证书与邮件协议证书是两条独立链路，仅给网站配置证书不会自动启用邮件客户端 TLS。证书续期后，将新的完整证书链和私钥同步到挂载目录，并重启应用使所有邮件进程加载新证书；本包不自动复制或续期证书。

## 首次使用

1. 在防火墙和云安全组放行实际使用的邮件 TCP 端口；需要公网邮件收发时，安装高级设置须允许邮件端口外部访问。Web 后端端口建议限制访问，只由反向代理访问。
2. 使用 HTTPS 地址和安装时填写的管理员账号登录。
3. 添加邮件域名，按后台提示配置 MX、SPF、HELO SPF、DKIM、DMARC，并向服务器提供商设置合适的 PTR。
4. 创建邮箱，分别测试外部收件、Webmail 发件以及第三方客户端的 SMTP/IMAP/POP3 TLS。
5. 检查容器健康状态及日志。健康检查涵盖 API 和邮件进程，不代表 DNS、TLS 证书或公网投递已验证通过。

## 数据与升级

持久化目录相对于实际应用安装目录：

- `data/`：SQLite 数据库、附件和应用状态。
- `mail/`：Maildir 邮件数据。
- `dkim/`：DKIM 签名密钥。

升级前停止应用并完整备份以上目录、安装参数中的加密主密钥和证书。SQLite 备份须包含可能存在的 WAL 等配套文件，建议停机复制整个目录。发生数据库结构变化后，不要直接用旧镜像打开新数据库；回滚应恢复对应版本镜像及升级前的完整备份。卸载或删除应用数据前另存备份。

## 本地应用验证与提交

将整个 `lanqin-email` 目录复制到 1Panel 的本地应用目录（默认 `/opt/1panel/resource/apps/local/`，以实际安装位置为准），更新应用列表后安装。

提交 PR 前请在真实 1Panel/Linux 环境验证：表单识别、镜像拉取、端口冲突检测、HTTPS 登录、首次管理员初始化、邮件收发、客户端 TLS、证书续期重启、容器重建后的数据保留及备份恢复。静态配置检查不能替代这些验证。

应用图标为本应用包绘制的简洁信封图标，可替换为项目正式 Logo。

项目：https://github.com/LanQin996/LanQin-Email
部署文档：https://github.com/LanQin996/LanQin-Email/blob/v1.1.6/deploy/README.md
