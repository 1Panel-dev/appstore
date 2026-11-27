## 产品介绍

**Watchtower** 是一个用于自动更新 Docker 容器的开源工具。它能够检测您运行的 Docker 容器是否有新版本可用，并自动更新这些容器，从而帮助您保持容器应用程序的安全性和稳定性。

## 从商店版 1.5.3 升级

从 1.24.2 起，商店使用社区维护的 `nickfedor/watchtower` 镜像。升级前请检查以下两项配置。

- 旧版默认监控所有 scope，新版默认只监控 scope 标签缺失、为空或值为 `none` 的容器。如果需要监控 `prod`，可以给 Watchtower 自身和目标容器都加上 `com.centurylinklabs.watchtower.scope=prod` 标签。[上游 scope 配置说明](https://watchtower.nickfedor.com/v1.24.2/advanced-features/running-multiple-instances/)
- 如果 Teams 通知的命令参数中包含 `--notification-msteams-data`，请在升级前移除，否则新版无法启动。对应的环境变量 `WATCHTOWER_NOTIFICATION_MSTEAMS_USE_LOG_DATA` 也已失效。[Teams 配置说明](https://watchtower.nickfedor.com/v1.24.2/notifications/deprecations/teams/)

## 主要功能

- **自动容器更新**：Watchtower 可以定期检查 Docker Hub 或者其他容器仓库中的镜像，如果发现新版本，它将自动下载新的镜像并更新正在运行的容器。
- **定期检查**：您可以配置 Watchtower 以定期检查容器，以确保容器始终处于最新状态。您可以设置检查的时间间隔。
- **邮件通知**：Watchtower 可以配置为在更新容器时发送邮件通知，以便您及时了解容器的变化情况。
- **灵活性**：您可以选择性地排除某些容器不受自动更新的影响，以满足特定需求。
- **日志记录**：Watchtower 会记录容器更新的日志，以便您可以随时查看更新历史。
- **支持多种容器仓库**：除了 Docker Hub，Watchtower 还支持其他容器仓库，因此您可以使用自己喜欢的容器镜像。
