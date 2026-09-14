## 默认账户密码

用户名: `admin`

密码: 前往应用商店，在应用的参数配置中获取

## 配置和使用说明

- admin 是临时管理员账户，首次登录后请新建一个账户，**并为其分配管理员角色**，再将 admin 账户删除。
  1. 左侧栏找到 Users, Add Users 创建一个账户
  2. 选择 Credentials 页签为账户设置密码
  3. 选择 Role mapping 页签，点击 Assign role，选择 Realm roles
  4. 勾选 admin (role_admin) 后 Assign
  5. 删除 admin 账户
- Keycloak 支持 i18n，但需要手动启用
  - 左侧栏找到 Realm settings, 选择 Localization 页签，启用 Internationalization 后选择你的支持区域

## 产品介绍

Keycloak 是一个开源的 **身份和访问管理 (IAM)** 平台，专为现代应用程序和服务设计。它的核心目标是简化应用程序的身份验证和授权流程，使开发者无需自行处理用户存储、认证逻辑等复杂的安全问题。

## 主要功能

- **标准化身份协议支持**: 原生支持 OpenID Connect、OAuth 2.0 和 SAML 等主流身份协议，使应用程序能够轻松集成单点登录 (SSO) 和社会化登录 (如 Google、GitHub 等) 。
- **用户联邦与集中管理**: 允许从外部用户存储 (如 LDAP、Active Directory、Kerberos) 同步用户，并提供统一的用户管理界面，简化用户生命周期管理 (创建、更新、删除、角色分配) 。
- **细粒度授权与访问控制**: 提供基于角色 (RBAC) 和属性 (ABAC) 的精细化授权策略，支持动态权限管理和策略决策，确保应用程序和服务的安全访问。
- **强身份验证机制**: 内置多因素认证 (MFA) 、条件访问、密码策略和身份验证流程定制，增强账户安全性，满足合规性要求。