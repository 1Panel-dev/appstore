## 产品介绍

**Gotify** 是一个简单的服务器来发送和接收消息（通过 WebSocket 实时发送和接收）

## 特征

- 通过 REST-API 发送消息
- 通过 WebSocket 接收消息
- 管理用户、客户端和应用程序
- [插件](https://gotify.net/docs/plugin)
- Web-UI -> [./ui](https://github.com/gotify/server/tree/master/ui)
- 发送消息的 CLI -> [gotify/cli](https://github.com/gotify/cli)
- Android-APP -> [gotify/android](https://github.com/gotify/android)

## 从 2.x 升级到 3.x

- 升级前请停止应用，并备份应用目录中的 `data` 和 `.env`。
- Gotify 3 不再支持 `config.yml`。1Panel 模板默认使用环境变量；如果自行添加过配置文件，请先按照官方文档使用 `migrate-config` 完成迁移。
- `GOTIFY_SERVER_SSL_LETSENCRYPT_HOSTS`、`GOTIFY_SERVER_CORS_ALLOWORIGINS`、`GOTIFY_SERVER_CORS_ALLOWMETHODS`、`GOTIFY_SERVER_CORS_ALLOWHEADERS` 和 `GOTIFY_SERVER_STREAM_ALLOWEDORIGINS` 改为 CSV 格式，例如将 `[GET, POST]` 改为 `GET,POST`。
- `GOTIFY_SERVER_RESPONSEHEADERS` 改为 JSON 格式，例如将 `{X-Custom-Header: "custom value"}` 改为 `{"X-Custom-Header":"custom value"}`；旧格式会导致 Gotify 3 启动失败。
- 如果旧实例启用了 Let's Encrypt，且 `.env` 中的 `GOTIFY_SERVER_SSL_LETSENCRYPT_CACHE` 仍为 `certs`，请先把旧容器内的 `/app/certs` 复制到应用目录的 `data/certs`，再将该变量改为 `data/certs`，避免重建容器时丢失证书和 ACME 账户数据。
- Gotify 3 调整了令牌展示、客户端敏感操作鉴权和消息分页行为。依赖 Gotify API 的脚本或客户端应在升级前检查兼容性。现有应用令牌和客户端令牌仍可继续使用。
- 官方迁移文档：https://gotify.net/docs/migrate-to-3
