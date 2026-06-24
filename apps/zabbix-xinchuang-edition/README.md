# Zabbix 7.0 信创版

企业级开源监控解决方案，支持网络、服务器、虚拟机和云服务的全方位监控。需外接数据库（TDSQL/MySQL/MariaDB），部署后自动初始化。Agent/Proxy 默认关闭，开启后容器启动。

---

## 组件说明

| 组件 | 说明 | 默认状态 |
|------|------|----------|
| zabbix-db-init | 数据库初始化（建表后保持运行） | 常驻运行 |
| zabbix-server | 监控数据采集与处理核心 | 常驻运行 |
| zabbix-web | Nginx + PHP Web 管理界面，开机自动装插件 | 常驻运行 |
| zabbix-agent | 本机监控 Agent | 可选（关闭时空转） |
| zabbix-proxy | 分布式 Proxy 节点 | 可选（关闭时空转） |

## 镜像说明

> **注意**：`zabbix/zabbix-server-mysql` 和 `zabbix/zabbix-web-nginx-mysql` 中的 "mysql" 表示编译了 MySQL 客户端支持，**不是数据库镜像**。本应用不包含数据库服务。

| 镜像 | 标签 |
|------|------|
| zabbix/zabbix-server-mysql | ubuntu-7.0-latest |
| zabbix/zabbix-web-nginx-mysql | ubuntu-7.0-latest |
| zabbix/zabbix-agent | ubuntu-7.0-latest |
| zabbix/zabbix-proxy-mysql | ubuntu-7.0-latest |

## 安装前提

- **必须**：已准备好外部数据库（支持 TDSQL / MySQL 5.7+ / MariaDB 10.5+）
- **推荐**：服务器内存 4GB 以上，CPU 2 核以上
- 表结构由 db-init 容器在首次部署时自动创建
- 若使用 Proxy，需额外准备一个独立的 Proxy 数据库

## 安装后

1. 浏览器访问 `http://<服务器IP>:<Web 界面端口>`
2. 默认登录：`Admin` / `zabbix`，**首次登录后立即修改密码！**

## 端口说明

| 端口 | 默认值 | 用途 |
|------|--------|------|
| Web UI | 8080 | Zabbix Web 管理界面 |
| Server | 10051 | Agent/Proxy 连接 Server |
| Agent | 10050 | 本机 Agent（需先启用） |
| Proxy | 10071 | Proxy 监听（需先启用，Passive 模式） |

## 启用 Agent / Proxy

默认关闭（容器空转，几乎不占资源）。启用步骤：
1. 1Panel → 已安装 → Zabbix → 参数
2. 改开关为「开启」，填端口和 Proxy 数据库信息
3. 点击「重建」

## 安装插件

上传 `zabbix-plugins-xxx.zip` 到 `data/plugins/` 目录，可通过 1Panel 文件管理或 SCP：

```bash
cp zabbix-plugins-xxx.zip /opt/1panel/apps/local/zabbix/zabbix/data/plugins/
```

1Panel → Zabbix → 重建，Web 启动时自动解压安装（PHP 内置 PharData，纯本地无需网络）。
完成后去 Zabbix Web → 管理 → 模块 → 扫描目录 → 激活。

## 故障排查

| 现象 | 原因 | 处理 |
|------|------|------|
| Server 状态一会是一会否 | 数据库 ha_node 表有残留 | 清理: `DELETE FROM ha_node;` |
| Agent/Proxy「不存在」 | 开关未开 | 参数里改为「开启」后重建 |
| db-init「异常」 | 已初始化后不需要重建 | 正常，容器会保持运行 |

## 相关链接

- 官网: https://www.zabbix.com
- 文档: https://www.zabbix.com/documentation/current/
- GitHub: https://github.com/zabbix/zabbix
