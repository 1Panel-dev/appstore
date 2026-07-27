# Zabbix Proxy 分布式监控代理
Zabbix分布式监控代理组件，适用于多机房、跨网段、内网隔离监控场景。采用主动上报模式，仅Proxy单向访问Zabbix Server，内网隔离环境更安全；应用依赖外置独立MySQL数据库，容器启动后自动完成数据库表初始化。

## 组件说明
| 组件 | 说明 | 默认状态 |
|------|------|----------|
| zabbix-proxy-mysql | Zabbix分布式监控代理，内置MySQL客户端 | 常驻运行 |

## 镜像说明
> 注意：镜像名称中的 `mysql` 仅代表内置MySQL客户端，**不自带数据库服务**，本应用必须外接独立MySQL。

| 镜像 | 标签 |
|------|------|
| zabbix/zabbix-proxy-mysql | alpine-7.0.27 |

## 安装前提
1. 提前准备**独立外置MySQL数据库**（支持MySQL 5.7+ / MariaDB 10.5+），禁止与Zabbix Server共用数据库；
2. 服务器最低配置：内存 ≥1G，CPU ≥1核；

## 安装后
1. 容器启动后自动连接外置数据库，自动创建Proxy所需数据表；
2. 启动后主动向填写的Zabbix Server注册代理节点；
3. 登录Zabbix Server网页后台，进入「管理-代理」即可查看本Proxy在线状态，分配对应内网主机监控。

## 端口说明
| 端口 | 默认值 | 用途 |
|------|--------|------|
| Proxy通信端口 | 10051 | Proxy与Zabbix Server数据上报通信端口 |

## 相关链接
- 官方官网：https://www.zabbix.com
- Proxy官方文档：https://www.zabbix.com/documentation/current/manual/distributed_monitoring/proxies
- 官方GitHub：https://github.com/zabbix/zabbix
