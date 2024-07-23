# Bytebase

**Bytebase** 是面向开发人员和 DBA 的数据库 CI/CD 解决方案。它是CNCF Landscape和Platform Engineering包含的唯一数据库 CI/CD 项目。

## Bytebase 系列由以下工具组成：

- **Bytebase 控制台**：供开发人员和 DBA 管理数据库开发生命周期的基于 Web 的 GUI。
- **Bytebase API**：提供 gRPC 和 RESTful API 来操作 Bytebase 的各个方面。
- **Bytebase CLI**：帮助开发人员将数据库更改集成到现有 CI/CD 工作流中的 CLI。
- **Bytebase GitHub App和SQL Review GitHub Action**：GitHub App 和 GitHub Action 用于检测 SQL 反模式并在 Pull Request 期间强制执行一致的 SQL 样式指南。
- **Terraform Bytebase 提供程序**：Terraform 提供程序使团队能够通过 Terraform 管理 Bytebase 资源。典型的设置包括团队使用 Terraform 从云供应商配置数据库实例，然后使用 Bytebase 提供程序准备这些实例以供应用程序使用。