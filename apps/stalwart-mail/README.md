# Stalwart 邮件服务器

**Stalwart 邮件服务器** 是一个支持 JMAP、IMAP4、POP3 和 SMTP 的开源邮件服务器解决方案，拥有丰富的现代功能。它使用 Rust 编写，设计注重安全性、速度、稳定性和可扩展性。

---

## 🚀 主要功能

- **完整协议支持**：JMAP、IMAP4rev2、POP3、SMTP 及 ManageSieve。
- **高级 SMTP 功能**：内置 DMARC、DKIM、SPF、ARC、TLS 报告支持，灵活的路由与过滤。
- **反垃圾邮件与钓鱼防护**：基于 LLM 的邮件分析、灰名单、垃圾邮件陷阱及 DNSBL 支持。
- **灵活的存储后端**：支持 PostgreSQL、SQLite、Redis、S3、ElasticSearch 等。
- **高安全性**：支持 S/MIME 与 OpenPGP 加密，ACME TLS 证书，自适应限流与自动 IP 封锁。
- **可扩展性与高可用性**：支持 Kubernetes，分片存储，自动故障恢复，无单点故障。
- **全面的身份验证支持**：OAuth2、OpenID Connect、LDAP、TOTP 双因素认证、ACL 与基于角色的访问控制。
- **监控与管理界面**：Web 控制面板，Prometheus 指标，OpenTelemetry 支持，日志查看器与告警系统。

---

## 📺 教学视频

您可以观看以下优质 YouTube 教学视频，了解如何安装与配置 Stalwart 邮件服务器：

- https://youtu.be/JA_V0GFUwWc?si=71KKiAV59mUoL0X4  
- https://youtu.be/PMoiJktvzDw?si=hVhQxdLuj7PJJALm

📌 *感谢视频创作者 —— 强烈推荐用于入门。*
