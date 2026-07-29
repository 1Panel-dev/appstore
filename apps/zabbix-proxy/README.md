# Zabbix Proxy 分布式监控代理

## 产品介绍

Zabbix 分布式监控代理组件，适用于多机房、跨网段和内网隔离监控场景。应用默认使用主动模式，由 Proxy 主动连接 Zabbix Server，并使用独立的外置 MySQL 或 MariaDB 数据库。

## 组件说明

| 组件 | 说明 | 默认状态 |
|------|------|----------|
| zabbix-proxy-mysql | Zabbix分布式监控代理，内置MySQL客户端 | 常驻运行 |

## 镜像说明

> 注意：镜像名称中的 `mysql` 仅代表内置 MySQL 客户端，**不自带数据库服务**，本应用必须连接独立的 MySQL 或 MariaDB 数据库。

| 镜像 | 标签 |
|------|------|
| zabbix/zabbix-proxy-mysql | alpine-7.4.5 |

## 安装前提

1. 准备独立数据库，MySQL/Percona 最低版本为 8.0.30，MariaDB 最低版本为 10.5.00，禁止与 Zabbix Server 共用数据库。
2. Zabbix Proxy 7.4.5 应与 Zabbix Server 7.4.x 配套使用，以保证完整兼容。
3. 填写 Zabbix Server 地址时，默认端口为 `10051`；使用非默认端口时，请填写为 `host:port`。
4. 确定 Proxy 名称，安装时填写的名称必须与 Zabbix Server 后台配置的代理名称完全一致（区分大小写）。

## 安装后

1. 容器启动后自动连接外置数据库，并初始化 Proxy 所需数据表。
2. 登录 Zabbix Server 管理后台，进入「管理 → 代理」，创建一个主动模式代理，代理名称必须与安装时填写的 Proxy 名称一致。
3. Proxy 会主动连接 Zabbix Server 并请求配置；连接成功后，可在代理列表中查看状态并为其分配监控主机。

## 端口说明

| 端口 | 默认值 | 用途 |
|------|--------|------|
| Proxy 数据接收端口 | 10051 | 供受监控 Agent、发送器等连接 Proxy |

## 相关链接

- 官方官网：https://www.zabbix.com
- Proxy官方文档：https://www.zabbix.com/documentation/7.4/en/manual/distributed_monitoring/proxies
- 官方GitHub：https://github.com/zabbix/zabbix
