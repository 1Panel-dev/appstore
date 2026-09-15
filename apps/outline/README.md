# Outline

## 产品介绍

适合成长型团队的实时协作知识库，支持 Markdown、集合、全文搜索、评论、附件、导入导出、API 和公开分享。

本应用包面向**局域网 / 异地组网快速部署**：内置预配置好的 Keycloak 认证服务，安装完成后用**用户名 + 密码**即可登录，不需要域名、HTTPS、邮箱、Passkey 或手工创建 OIDC 客户端。

## 主要功能

- 文档、集合、实时协作编辑。
- PostgreSQL 全文搜索。
- 评论、通知、权限和公开分享。
- 附件上传、导入导出、API Key。
- 内置账号密码认证，可自助添加成员。

## 安装前准备

只需要 1Panel 中已经安装的服务：

- PostgreSQL 14 或更高版本。
- Redis 4 或更高版本。

不需要域名、证书、SMTP 账号或第三方身份提供方。

## 安装参数

| 参数 | 默认值 | 说明 |
| --- | --- | --- |
| Outline HTTP 端口 | `12115` | 知识库访问端口 |
| Keycloak HTTP 端口 | `12116` | 登录服务端口 |
| PostgreSQL 服务 | 选择已有实例 | Outline 数据库 |
| Redis 服务 | 选择已有实例 | 缓存与队列 |
| Outline 公网地址 | `http://服务器IP:12115` | 浏览器实际访问地址 |
| Keycloak 管理员账号 | `admin` | 同时作为初始 Outline 账号 |
| Keycloak 管理员密码 | `outline-admin` | 同时作为初始 Outline 账号密码，**建议修改** |
| Outline 新用户的界面语言 | `zh_CN` | 下拉选择，可选项为 Outline 官方支持的全部 26 种语言 |

数据库名、数据库用户、数据库密码由 1Panel 自动生成。

Keycloak 的公开地址、OIDC 客户端密钥和回调地址都由安装脚本自动推导生成，不需要手工填写。

## 首次登录

安装完成后：

1. 浏览器打开 `http://<服务器IP或组网IP>:12115`。
2. 页面会自动跳转到登录页，输入：
   - 用户名：安装时填写的 Keycloak 管理员账号
   - 密码：安装时填写的 Keycloak 管理员密码
3. 登录成功后自动进入 Outline，工作区和管理员账号会自动创建。

不需要创建工作者、不需要收邮件、不需要点击任何确认链接。

## 界面语言

安装参数里的「Outline 新用户的界面语言」决定新用户的默认界面语言，默认 `zh_CN`（简体中文），可选项是 Outline 官方支持的全部 26 种语言。

Keycloak 的语言会自动跟随，不需要手工配置：

| 场景 | 登录页语言 |
| --- | --- |
| 浏览器语言在支持列表内（如简体中文、英文） | 跟随浏览器 |
| 浏览器语言不在支持列表内 | 跟随安装参数：中文安装为中文，其他一律英文 |

Keycloak 自带的翻译远少于 Outline，所以非中文安装统一回退到英文，不会出现中文登录页。登录页右下角始终提供语言选择器，用户可以自行切换。

Keycloak 管理控制台（12116）的默认语言同样跟随安装参数，由 Keycloak 容器启动后自动写入 master realm。整个过程在同一个容器内完成，不会产生额外的或已退出的容器。

如果自动配置没有生效（例如你后来改过管理员密码），可以手工开启：

1. 登录 12116 控制台
2. 左上角切到 `master` realm
3. `Realm settings` → `Localization` → 打开 `Internationalization`
4. 按需勾选 `Supported locales`，并设置 `Default locale`
5. 保存后刷新页面，登录页就会出现语言选择器

## 添加成员

1. 打开 `http://<服务器IP或组网IP>:12116`，进入 Keycloak 管理控制台。
2. 使用安装时设置的管理员账号和密码登录。
3. 左上角切换到 `outline` 这个 realm。
4. 进入 `Users`，点击 `Add user`，填写：
   - Username
   - Email
   - First name / Last name
   - 打开 `Email verified`
5. 保存后进入 `Credentials` 标签，点击 `Set password`，设置密码并关闭 `Temporary`。
6. 新成员打开 `http://<服务器IP或组网IP>:12115`，用自己的用户名和密码登录即可。

新成员首次登录时，Outline 会自动把账号加入同一个工作区，无需管理员在 Outline 内再次邀请。

## 修改密码

初始密码只在第一次安装时写入 Keycloak。安装完成后要改密码，请在 Keycloak 控制台修改：

- Keycloak 控制台管理员：`Realm settings` → `Users` → 安装时设置的管理员账号 → `Credentials`
- Outline 登录用户：realm 切到 `outline` → `Users` → 对应用户 → `Credentials`

直接在 1Panel 表单里修改密码不会覆盖已经初始化过的 Keycloak 数据。

## 邮件通知

邮件功能是可选的。不配置 SMTP 时：

- 登录不受影响，仍然使用账号密码。
- 不会有邮件通知和邮件邀请，站内通知正常。

需要邮件通知时，在 1Panel 参数中填写 SMTP 主机、端口、用户名、密码和发件人地址即可。

## 反向代理与 HTTPS

局域网或异地组网可以直接使用 `http://<IP>:12115`，不需要反向代理。

如果要对公网开放，建议在 1Panel 中创建反向代理站点并启用 HTTPS，并且**同时为 Outline 和 Keycloak 配置域名**，例如：

| 域名 | 反向代理目标 |
| --- | --- |
| Outline 域名 | `http://127.0.0.1:12115` |
| Keycloak 域名 | `http://127.0.0.1:12116` |

修改访问地址后，需要把 1Panel 参数中的 Outline 公网地址改成新的地址并重建应用，让 Keycloak 的回调地址同步更新。

Outline 站点的 Nginx 高级配置需要包含：

```nginx
proxy_http_version 1.1;
proxy_set_header Upgrade $http_upgrade;
proxy_set_header Connection "upgrade";
proxy_set_header Host $host;
proxy_set_header X-Real-IP $remote_addr;
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
proxy_set_header X-Forwarded-Proto $scheme;

client_max_body_size 250m;
proxy_read_timeout 600s;
proxy_send_timeout 600s;
```

## 文件存储

默认使用本地文件存储，附件位于应用目录的 `data/outline`。默认上传、导入和工作区导入限制均为 250 MiB，需要和反向代理的 `client_max_body_size` 保持一致。

## 自定义环境变量

`env/docker.env` 是留给用户的自定义环境变量文件，默认是空的，可以直接写入 Outline 官方支持的任意变量：

```
LOG_LEVEL=debug
OIDC_USERNAME_CLAIM=email
```

它会在生成配置（`data/oidc.env`）之后加载，因此可以覆盖 OIDC 等由脚本生成的变量。

但 `docker-compose.yml` 里 `environment:` 段已声明的变量优先级最高，例如 `DEFAULT_LANGUAGE`、`FILE_STORAGE_UPLOAD_MAX_SIZE`、`RATE_LIMITER_ENABLED`。要改这些请使用安装参数或直接编辑 compose 文件，写进 `env/docker.env` 不会生效。

## 备份与升级

升级前建议备份：

1. PostgreSQL 数据库。
2. `data/outline`：附件。
3. `data/keycloak`：Keycloak 账号、密码和 realm 配置。
4. `data/secrets`：Outline 密钥和 OIDC 客户端密钥。

`data/keycloak` 或 `data/secrets` 丢失会直接影响登录，请务必一起备份。

升级脚本会保留现有密钥、重新生成与当前参数匹配的 Keycloak realm 配置，并且不会删除 Docker 卷。Outline 的数据库迁移由官方容器在启动时自动执行。

## 安全建议

- 局域网测试可以直接使用 HTTP；对公网开放时请启用 HTTPS。
- 12116 是认证服务端口，登录时必须能被浏览器访问，但不要暴露到无需认证的公网环境。
- 不要把 PostgreSQL 和 Redis 端口公开到公网。
- 把默认管理员密码改成强密码。
- 不要使用 `latest` 镜像标签。
