## 产品介绍

**Planka** 是一款开源、自托管的项目管理工具，类似 Trello，基于 **Node.js、React 和 PostgreSQL** 构建。  

## 主要特性

- **可视化看板**：拖拽操作直观，管理任务更高效  
- **团队协作**：支持多用户，灵活权限管理  
- **实时同步**：WebSockets 确保多端数据即时更新  
- **轻量高效**：运行流畅，占用资源少  

## 持久化说明

- 当前应用版本基于 Planka V2 的官方持久化方式，统一挂载 `./data` 到容器内的 `/app/data`。
- 如果宿主机使用目录挂载而不是 Docker volume，首次启动前建议执行 `chown -R 1000:1000 ./data`，避免容器内默认 `node` 用户没有写权限。

## 升级说明

- 如果你是从旧版 Planka 升级，旧模板通常会把数据分别保存在 `user-avatars`、`project-background-images`、`attachments` 等目录中。
- 升级到当前版本前，建议先完整备份数据库和旧数据目录，再按照 Planka 官方 V2 升级文档将旧文件迁移到新的 `/app/data` 结构中，否则可能导致头像、背景图或附件无法继承。
- 官方升级文档：https://docs.planka.cloud/docs/upgrade-to-v2/docker
