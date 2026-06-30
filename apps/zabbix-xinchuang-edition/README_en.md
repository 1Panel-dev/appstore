# Zabbix 7.0 Xinchuang Edition

Enterprise-class open source monitoring solution for networks, servers, virtual machines, and cloud services. Requires an external database (TDSQL/MySQL/MariaDB), which is auto-initialized after deployment. Agent and Proxy are disabled by default; containers are started after enabled.

---

## Components

| Component | Description | Default State |
|-----------|-------------|---------------|
| zabbix-db-init | Database initialization (keeps running after table creation) | Always on |
| zabbix-server | Core monitoring data collection and processing | Always on |
| zabbix-web | Nginx + PHP web management interface, auto-installs plugins on startup | Always on |
| zabbix-agent | Local host monitoring agent | Optional (idle when disabled) |
| zabbix-proxy | Distributed proxy node | Optional (idle when disabled) |

## Image Notes

> **Note**: The "mysql" in `zabbix/zabbix-server-mysql` and `zabbix/zabbix-web-nginx-mysql` indicates compiled MySQL client support, **not a database image**. This app does not include a database service.

| Image | Tag |
|-------|-----|
| zabbix/zabbix-server-mysql | ubuntu-7.0-latest |
| zabbix/zabbix-web-nginx-mysql | ubuntu-7.0-latest |
| zabbix/zabbix-agent | ubuntu-7.0-latest |
| zabbix/zabbix-proxy-mysql | ubuntu-7.0-latest |

## Prerequisites

- **Required**: An external database must be available (supports TDSQL / MySQL 5.7+ / MariaDB 10.5+)
- **Recommended**: Server with 4GB+ RAM and 2+ CPU cores
- Table structures are automatically created by the db-init container on first deployment
- If using Proxy, a separate Proxy database must be prepared

## Post-Installation

1. Open browser and navigate to `http://<server-ip>:<web-port>`
2. Default credentials: `Admin` / `zabbix` — **change the password immediately after first login!**

## Ports

| Port | Default | Purpose |
|------|---------|---------|
| Web UI | 8080 | Zabbix web management interface |
| Server | 10051 | Agent/Proxy connection to Server |
| Agent | 10050 | Local Agent (must be enabled first) |
| Proxy | 10071 | Proxy listener (must be enabled first, Passive mode) |

## Enabling Agent / Proxy

Disabled by default (containers idle with minimal resource usage). To enable:

1. 1Panel → Installed Apps → Zabbix → Settings
2. Toggle switch to "Enabled", enter port and Proxy database info
3. Click "Rebuild"

## Installing Plugins

Upload `zabbix-plugins-xxx.zip` to the `data/plugins/` directory via 1Panel file manager or SCP:

```bash
cp zabbix-plugins-xxx.zip /opt/1panel/apps/local/zabbix/zabbix/data/plugins/
```

1Panel → Zabbix → Rebuild. Plugins are auto-extracted and installed on web startup (PHP built-in PharData, fully local, no network required).
Then go to Zabbix Web → Administration → Modules → Scan Directory → Activate.

## Troubleshooting

| Symptom | Cause | Resolution |
|---------|-------|------------|
| Server status toggles on/off | Stale records in database `ha_node` table | Clean up: `DELETE FROM ha_node;` |
| Agent/Proxy "not available" | Not enabled | Toggle to "Enabled" in settings, then rebuild |
| db-init "abnormal" | Already initialized, no rebuild needed | Normal, container will keep running |

## Links

- Website: https://www.zabbix.com
- Documentation: https://www.zabbix.com/documentation/current/
- GitHub: https://github.com/zabbix/zabbix
