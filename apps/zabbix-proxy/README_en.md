# Zabbix Proxy Distributed Monitoring Proxy
Zabbix distributed monitoring proxy component, suitable for monitoring scenarios with multiple computer rooms, cross-network segments, and isolated internal networks. It adopts the active reporting mode where only the Proxy initiates one-way access to the Zabbix Server, delivering higher security for isolated intranet environments. This component relies on an external independent MySQL database, and automatic initialization of database tables will be completed after the container starts.

## Component Description
| Component | Description | Default Status |
| ---- | ---- | ---- |
| zabbix-proxy-mysql | Zabbix distributed monitoring proxy with built-in MySQL client | Persistently running |

## Image Instructions
Note: The `mysql` suffix in the image name only indicates a built-in MySQL client. The image does NOT include a database service. An external independent MySQL instance is mandatory for this application.

| Image | Tag |
| ---- | ---- |
| zabbix/zabbix-proxy-mysql | alpine-7.4.5 |

## Prerequisites for Installation
1. Prepare an independent external MySQL database in advance (MySQL 5.7+ / MariaDB 10.5+ supported). Sharing a database with Zabbix Server is prohibited.
2. Minimum server specifications: Memory ≥ 1 GB, CPU ≥ 1 core.

## Post-Installation Behavior
1. The container automatically connects to the external database after startup and creates all data tables required by the Proxy.
2. The Proxy actively registers itself as an agent node to the specified Zabbix Server after launch.
3. Log in to the Zabbix Server web backend, navigate to **Administration → Proxies** to check the online status of this Proxy and assign intranet hosts for monitoring.

## Port Specifications
| Port Type | Default Value | Purpose |
| ---- | ---- | ---- |
| Proxy Communication Port | 10051 | Data reporting communication port between Proxy and Zabbix Server |

## Relevant Links
- Official Website: https://www.zabbix.com
- Official Proxy Documentation: https://www.zabbix.com/documentation/current/manual/distributed_monitoring/proxies
- Official GitHub Repository: https://github.com/zabbix/zabbix
