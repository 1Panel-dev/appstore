## 产品介绍

**Memoh** 是一个常驻运行的、多成员、结构化长记忆、容器化的 AI Agent 系统平台。你可以创建多个 AI Bot，并通过 Telegram、Discord、飞书（Lark）、QQ、Matrix、企业微信、微信、Email 或内置 Web 界面与它们交互。

每个 Bot 都拥有独立的容器和记忆系统，可以在自己的容器中读写文件、执行命令、访问网络，并通过 MCP 连接外部工具。它更像是给每个 Bot 一台自己的电脑和大脑。

本应用包采用完整推荐部署：

- `postgres`：关系数据库
- `server`：Memoh 主服务，内置 Agent 与 workspace containerd
- `web`：Web 管理界面
- `qdrant`：向量数据库
- `sparse`：本地 sparse memory 编码服务
- `browser`：浏览器自动化网关

## 主要特点

- 多 Bot、多用户、多渠道统一接入
- 结构化长期记忆与混合检索能力
- 每个 Bot 独立容器运行，支持文件与命令操作
- 支持 MCP、技能、子代理和浏览器自动化
- 提供可视化 Web 管理与聊天界面

## 安装前须知

- `Memoh server` 需要 `privileged: true` 和 `pid: host`，请确认你的 1Panel 宿主机允许这类容器权限
- 首次启动耗时会比普通应用更长，因为需要初始化数据库、向量库和浏览器网关
- 完整推荐版会拉取较大的 `browser` 与 `sparse` 镜像，建议预留至少 `8GB` 以上可用磁盘空间
- 默认会暴露两个端口：`Web UI` 默认 `8082`，`API` 默认 `8080`

## 安装后访问

- Web UI：`http://<你的主机IP>:<Web UI 端口>`
- API：`http://<你的主机IP>:<API 端口>`

默认管理员账号与密码由安装表单决定。

## 配置说明

安装表单会生成 `config.toml`，主要包含：

- 管理员账号、密码、邮箱
- JWT 密钥
- PostgreSQL 密码
- 时区
- 可选 workspace 镜像仓库前缀

本应用已默认将 `Workspace Registry` 配置为 `docker.1ms.run`，用于加速 Bot 工作容器基础镜像的拉取；如你的网络环境支持直连，也可以按需自行调整。

## 升级建议

- 升级前备份 `data/` 目录
- 升级脚本默认保留已生成的 `config.toml`
- 若后续版本需要新增配置项，建议先在测试环境验证
