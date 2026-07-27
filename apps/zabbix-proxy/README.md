# Zabbix Proxy 分布式监控代理
## 功能介绍
独立部署Zabbix Proxy，用于多机房、跨网段分布式监控，采用**主动上报模式**，仅需Proxy单向访问Zabbix Server，内网隔离场景更安全。

## 兼容说明
1. 完美兼容1Panel应用商店一键部署的Zabbix服务端；
2. 数据库外置独立MySQL，不与服务端共用数据库；
3. 自动复用1panel-network全局网络，无需额外网络配置。

## 环境参数说明
1. ZBX_HOSTNAME：Proxy标识名称，Zabbix后台创建代理名称必须完全一致；
2. ZBX_SERVER_HOST：Zabbix Server服务内网IP；
3. PANEL_DB_HOST：存储Proxy监控数据的MySQL数据库地址；
4. PANEL_DB_PORT：MySQL数据库端口；
5. PANEL_DB_USER_PASSWORD：MySQL数据库root账号密码。
