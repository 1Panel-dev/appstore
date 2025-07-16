# 注意事项

- 使用端口访问会导致控制台无法显示，请修改为使用域名访问
- 必须使用反代且配置HTTPS

# ZITADEL

**ZITADEL** 是开源的身份管理基础设施，提供安全的认证、授权和多租户支持，适用于 B2B/B2C 场景。

## 核心功能

- **多租户管理**：支持企业级用户和团队管理
- **认证协议**：OpenID Connect、OAuth2.x、SAML2、LDAP、SCIM 2.0
- **安全特性**：Passkeys (FIDO2)、OTP、U2F、设备授权
- **自服务**：用户自助注册/登录、管理员控制台
- **审计日志**：事件溯源存储，支持 SOC/SIEM 集成## 部署方式
- **自托管**：支持 Linux、MacOS、Docker、Kubernetes 等
- **云服务**：ZITADEL Cloud 提供 US/EU/AU/CH 数据区域，含免费 tier
