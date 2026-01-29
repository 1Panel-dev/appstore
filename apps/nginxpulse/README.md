# NginxPulse

轻量级 Nginx 访问日志分析与可视化面板，提供实时统计、PV 过滤、IP 归属地与客户端解析。

## 访问地址
- Web UI: `http://<服务器IP>:<对外端口>`

## 端口
- 容器端口：8088（Web UI）

## 挂载目录（必须）
- `日志目录（只读）`：`/share/logs`
- `配置目录`：`/app/configs`

## 持久化目录
- `/app/var/nginxpulse_data`（应用数据）
- `/app/var/pgdata`（内置 PostgreSQL 数据，仅在不设置 `DB_DSN` 时使用）

## 环境变量
- `PUID` / `PGID`：容器内应用用户的 UID/GID（可选）
- `TZ`：时区（可选，默认 `Asia/Shanghai`）
- `DB_DSN`：外置数据库连接串（可选，设置后将禁用内置 PostgreSQL）

`DB_DSN` 示例：
```
postgres://user:password@host:5432/nginxpulse?sslmode=disable
```

## 初始化说明
- 首次启动无需账号密码，进入初始化向导配置后即可使用。

## 注意事项
- 必须挂载日志目录且权限可读，否则无法解析日志。
- 使用外置数据库时，`/app/var/pgdata` 不再使用（可保留挂载）。
- 建议挂载宿主机时区文件以避免时间解析错误。
