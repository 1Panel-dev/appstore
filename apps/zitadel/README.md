## 安装说明

### V4

1. 确认服务器使用 Docker Engine 24 或更高版本、Docker Compose v2，并至少有 2 GB 内存；选择 PostgreSQL 14–18 数据库服务，再填写计划用于 ZITADEL 的公网 HTTPS 域名。
2. 安装完成后，在 1Panel 中创建反向代理网站，目标填写 `http://127.0.0.1:<应用 HTTP 端口>` 并启用 HTTPS。1Panel 会为 HTTPS 网站启用 HTTP/2。
3. 在网站配置中删除默认生成的 `location /`，并按 ZITADEL 官方 NGINX 示例添加以下配置。普通 HTTP/1.1 `proxy_pass` 不支持 ZITADEL 的原生 gRPC API。

```nginx
location / {
    grpc_pass grpc://127.0.0.1:<应用 HTTP 端口>;
    grpc_set_header Host $host;
    grpc_set_header X-Forwarded-Proto https;
}
```

4. 使用安装时创建的初始组织管理员登录。默认用户名填写 `zitadel-admin` 时，完整登录名为 `zitadel-admin@zitadel.<公网域名>`。初始管理员配置只在首次初始化时生效。

V4 包使用 ZITADEL 主程序内置且官方仍支持的 Login V1，因此仅运行一个 ZITADEL 容器。需要 Login V2 或依赖它的功能时，应按官方 [Login V2 迁移指南](https://zitadel.com/docs/self-hosting/manage/adopt-login-v2)单独规划部署。其他要求请参阅 ZITADEL 官方的 [NGINX 反向代理文档](https://zitadel.com/docs/self-hosting/manage/reverseproxy/nginx)和[系统要求](https://zitadel.com/docs/self-hosting/manage/requirements)。

## 注意事项

- V4 安装项按 `https://<公网域名>:443` 初始化。安装表单中的域名必须与用户实际访问的域名完全一致，否则会出现 `Instance not found`。请勿使用应用端口直接访问。
- 公网域名和初始管理员参数应在首次安装前确认；首次初始化后不能通过 1Panel 应用参数直接修改。
- 主密钥必须恰好为 32 个字符，安装脚本会在首次启动前安全生成并保存到 `data/masterkey.env`；该文件会随应用备份保存，首次初始化后不得修改或丢失。
- V4 包显式关闭实例级 Login V2，并继续使用 V4 主程序内置且官方支持的 Login V1；该设置仅在首次初始化时生效。
- V4 是独立版本，不支持从应用商店现有 V3.3.2 直接一键升级。迁移已有实例前必须备份数据库，先升级至最新 V3.4.x（最低 V3.4.1），等待旧 Token 和会话过期或安排用户重新登录，再按官方流程先运行 V4 `setup` 完成迁移，然后启动 V4。请参阅[官方 V3 升级 V4 指南](https://zitadel.com/docs/self-hosting/manage/upgrade-v3-to-v4)。
- V2 与 V3 同样需要使用与安装配置一致的域名，并通过 HTTPS 反向代理访问。

## 产品介绍

**ZITADEL** 是开源身份基础设施，提供身份认证、授权、多租户和审计能力，适用于 B2B 与 B2C 场景。

## 主要功能

- 支持 OpenID Connect、OAuth 2.x 和 SAML 2.0。
- 支持 Passkeys、多因素认证和无密码登录。
- 支持多租户组织、项目、角色和权限管理。
- 提供管理控制台、自助登录界面、API 和审计事件。
