## 产品介绍

**Stalwart 邮件服务器** 是一个支持 JMAP、IMAP4、POP3 和 SMTP 的开源邮件服务器解决方案，拥有丰富的现代功能。它使用 Rust 编写，设计注重安全性、速度、稳定性和可扩展性。

## 主要功能

- **完整协议支持**：JMAP、IMAP4rev2、POP3、SMTP 及 ManageSieve。
- **高级 SMTP 功能**：内置 DMARC、DKIM、SPF、ARC、TLS 报告支持，灵活的路由与过滤。
- **反垃圾邮件与钓鱼防护**：基于 LLM 的邮件分析、灰名单、垃圾邮件陷阱及 DNSBL 支持。
- **灵活的存储后端**：支持 PostgreSQL、SQLite、Redis、S3、ElasticSearch 等。
- **高安全性**：支持 S/MIME 与 OpenPGP 加密，ACME TLS 证书，自适应限流与自动 IP 封锁。
- **可扩展性与高可用性**：支持 Kubernetes，分片存储，自动故障恢复，无单点故障。
- **全面的身份验证支持**：OAuth2、OpenID Connect、LDAP、TOTP 双因素认证、ACL 与基于角色的访问控制。
- **监控与管理界面**：Web 控制面板，Prometheus 指标，OpenTelemetry 支持，日志查看器与告警系统。

## 安装步骤

1. 安装 Stalwart 时，请确保没有其他应用程序占用相同的端口。如果发生端口冲突（例如与 OpenResty 等 Web 服务器），您可以将 Stalwart 的默认端口更改为其他可用端口。

![](https://i.imgur.com/v8IiDmI.png)

2. 在初始设置过程中，请向下滚动并**启用以下选项**：

- **高级设置**
- **外部访问**

![](https://i.imgur.com/2wVMb3G.png)

3. 安装完成并启动 Stalwart 后，请**查看应用日志**。如果日志内容类似下图，说明安装成功，Stalwart 正在运行。

![](https://i.imgur.com/QM1Euld.png)

> *请保存日志中显示的账户信息，稍后登录时需要使用。*

4. 如果您的系统启用了**防火墙**，请确保使用 **TCP 协议** 打开 Stalwart 所需的端口，以确保邮件服务器正常运行。

![](https://i.imgur.com/4aYSKN2.png)

> *在此示例中，端口 `443` 已被 OpenResty 占用，因此我将 Stalwart 的端口更改为 `8443`。*

5. 设置完成后，在浏览器中打开您之前设置的地址和端口。登录时，请使用**第 3 步日志中显示的用户名和密码**。

![](https://i.imgur.com/0jrSZLt.png)

## 高级设置与配置

有关进一步的配置，可以参考以下两个视频：

- https://youtu.be/JA_V0GFUwWc?si=71KKiAV59mUoL0X4  
- https://youtu.be/PMoiJktvzDw?si=hVhQxdLuj7PJJALm
