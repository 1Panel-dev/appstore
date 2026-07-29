# Zabbix Proxy Distributed Monitoring Proxy

## Introduction

Zabbix Proxy is a distributed monitoring component for multi-site, cross-network, and isolated intranet environments. It runs in active mode by default, connects outbound to Zabbix Server, and uses a separate external MySQL or MariaDB database.

## Component Description

| Component | Description | Default Status |
| ---- | ---- | ---- |
| zabbix-proxy-mysql | Zabbix distributed monitoring proxy with built-in MySQL client | Persistently running |

## Image Instructions

Note: The `mysql` suffix in the image name only indicates a built-in MySQL client. The image does not include a database service. A separate MySQL or MariaDB database is required.

| Image | Tag |
| ---- | ---- |
| zabbix/zabbix-proxy-mysql | alpine-7.4.5 |

## Prerequisites for Installation

1. Prepare a separate database. The minimum supported versions are MySQL/Percona 8.0.30 and MariaDB 10.5.00. Do not share the Zabbix Server database.
2. Use Zabbix Proxy 7.4.5 with Zabbix Server 7.4.x to ensure full compatibility.
3. The default Zabbix Server port is `10051`. For a non-default port, enter the server address as `host:port`.
4. Choose a Proxy name. The value entered during installation must exactly match the proxy name configured in the Zabbix Server frontend; names are case-sensitive.

## Post-Installation Behavior

1. The container automatically connects to the external database after startup and creates all data tables required by the Proxy.
2. In the Zabbix Server frontend, go to **Administration → Proxies** and create an active proxy whose name exactly matches the Proxy name entered during installation.
3. The Proxy connects to Zabbix Server and requests its configuration. After the connection succeeds, check its status and assign monitored hosts in the proxy list.

## Port Specifications

| Port Type | Default Value | Purpose |
| ---- | ---- | ---- |
| Proxy Data Receiving Port | 10051 | Used by monitored agents, senders, and other clients connecting to the Proxy |

## Relevant Links

- Official Website: https://www.zabbix.com
- Official Proxy Documentation: https://www.zabbix.com/documentation/7.4/en/manual/distributed_monitoring/proxies
- Official GitHub Repository: https://github.com/zabbix/zabbix
