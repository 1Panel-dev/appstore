# NginxPulse

A lightweight Nginx access log analysis and visualization panel with real-time stats, PV filtering, IP geolocation, and client parsing.

## Access
- Web UI: `http://<server-ip>:<public-port>`

## Ports
- Container port: 8088 (Web UI)

## Required Mounts
- `Logs (read-only)`: `/share/logs`
- `Configs`: `/app/configs`

## Persistent Data
- `/app/var/nginxpulse_data` (app data)
- `/app/var/pgdata` (embedded PostgreSQL data, only used when `DB_DSN` is not set)

## Environment Variables
- `PUID` / `PGID`: UID/GID for the app user (optional)
- `TZ`: time zone (optional, default `Asia/Shanghai`)
- `DB_DSN`: external database DSN (optional; disables embedded PostgreSQL when set)

`DB_DSN` example:
```
postgres://user:password@host:5432/nginxpulse?sslmode=disable
```

## Initialization
- No default account required. Complete the initial setup wizard after the first start.

## Notes
- The logs directory must be mounted and readable, otherwise log parsing will fail.
- When using an external database, `/app/var/pgdata` is not used (keeping the mount is harmless).
- Mount the host time zone files to avoid time parsing issues.
