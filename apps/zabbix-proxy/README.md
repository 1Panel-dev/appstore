# Zabbix Proxy 分布式监控代理
企业级分布式监控代理组件，适配多机房、跨网段、内网隔离场景。采用主动上报模式，仅Proxy单向访问Zabbix Server，内网安全边界清晰，无需服务器暴露入站端口。需外置独立MySQL数据库，部署后自动完成数据库初始化。

## 组件说明
| 组件 | 说明 | 默认状态 |
|------|------|----------|
| zabbix-proxy-mysql | Zabbix分布式监控代理，内置MySQL客户端 | 常驻运行 |

## 镜像说明
> 注意：`zabbix/zabbix-proxy-mysql` 中 `mysql` 代表编译了MySQL客户端支持，并非内置数据库镜像，本应用不自带数据库服务。

| 镜像 | 标签 |
|------|------|
| zabbix/zabbix-proxy-mysql | alpine-7.0.27 |

## 安装前提
1. 必须提前准备**独立外置MySQL数据库**（支持MySQL 5.7+/MariaDB 10.5+），不可与Zabbix Server共用数据库；
2. 服务器推荐内存1G以上，CPU 1核及以上；
3. 网络权限：Proxy节点可单向连通Zabbix Server 10051端口，Server无需访问Proxy。

## 安装后
1. 部署完成后自动连接指定数据库，自动初始化Proxy所需数据表；
2. 在安装时填写正确的Zabbix Server地址与通信端口，启动后自动向服务端注册Proxy节点；
3. 可在Zabbix Server管理页面「代理」菜单查看在线状态，配置对应主机监控。

## 端口说明
| 端口 | 默认值 | 用途 |
|------|--------|------|
| Proxy通信端口 | 10051 | Proxy与Zabbix Server数据通信端口 |

## 常见问题处理
| 现象 | 原因 | 处理方案 |
|------|------|----------|
| Proxy无法连接Zabbix Server | 网络不通、防火墙拦截、Server地址填写错误 | 检查Proxy到Server 10051端口连通性，核对安装时填写的服务端地址 |
| 数据库连接失败 | MySQL地址/账号/密码错误、数据库未提前创建 | 确认外置MySQL服务正常，核对数据库连接参数，手动提前创建空数据库 |
| Proxy节点在Server显示离线 | Proxy未正常上报数据 | 查看容器日志，确认数据库初始化完成、服务端通信端口放行 |

## 相关链接
- 官网：https://www.zabbix.com
- 官方文档（Proxy模块）：https://www.zabbix.com/documentation/current/manual/distributed_monitoring/proxies
- GitHub：https://github.com/zabbix/zabbix
