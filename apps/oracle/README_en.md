## Overview

**Oracle AI Database 23ai Free** is Oracle's free, container-ready edition of Oracle Database. It is a converged database: relational, JSON, graph (Property Graph) and vector (AI Vector Search) data all live in a single instance — a good fit for development, testing, teaching and small to medium business systems.

This app is built on the community-maintained [`gvenzl/oracle-free`](https://github.com/gvenzl/oci-oracle-free) image, which adds multi-arch builds (amd64 / arm64), a health check script, automatic application user creation and an init-script directory on top of Oracle's own image.

## Version choice

| Version               | Image                                         | Status              | Notes                                                                                                                                            |
| --------------------- | --------------------------------------------- | ------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------ |
| **23ai Free 23.26.3** | `gvenzl/oracle-free:23.26.3`                  | ✅ Used by this app | Actively maintained (built 2026-08), amd64 + arm64, ships `healthcheck.sh` and init-script support                                               |
| 21c XE 21.3.0         | `gvenzl/oracle-xe:21.3.0`                     | ⚠️ Not recommended  | Image not rebuilt since 2024-10, amd64 only, 21c is no longer the mainstream release                                                             |
| 26ai Free             | `container-registry.oracle.com/database/free` | 🔵 Worth watching   | Newest free edition, but must be pulled from Oracle's registry (slow in some regions, no Docker Hub mirror); no `APP_USER` convenience variables |

## Free edition limits

These are hard license limits — exceeding them fails at runtime, so it is not a production database:

- Max **2 CPU threads**
- Max **2 GB database RAM**
- Max **12 GB of user data** (`ORA-12954` when exceeded)
- Single instance, no RAC / Data Guard

Reserve at least **2 GB of free memory** for the container; 4 GB+ on the host is recommended.

## After installation

| Item               | Value                                                                |
| ------------------ | -------------------------------------------------------------------- |
| Port               | The port you entered (default `1521`)                                |
| Service name (PDB) | `FREEPDB1` (or the additional PDB name you entered)                  |
| CDB service / SID  | `FREE`                                                               |
| Admin users        | `SYS` / `SYSTEM`, password = the "SYS / SYSTEM Password" you entered |
| Application user   | The user you entered (default `appuser`), created inside `FREEPDB1`  |

> The first startup initializes the database and usually takes **3–8 minutes** (longer with an extra PDB). The container stays in `starting` state until the log shows `DATABASE IS READY TO USE!`.

## Connecting

Host = server IP (containers on the same network can use `oracle`), port = the port you entered (default `1521`), service name `FREEPDB1`, SID `FREE`.

> "Service name" and "SID" are two different fields in Oracle clients: **service name = `FREEPDB1`, SID = `FREE`**. Swapping them prevents the connection.

### sqlplus inside the container

```bash
# as administrator
docker exec -it oracle sqlplus sys/<sys_password>@//localhost:1521/FREEPDB1 as sysdba
# or with system
docker exec -it oracle sqlplus system/<sys_password>@//localhost:1521/FREEPDB1
# as the application user
docker exec -it oracle sqlplus appuser/<app_user_password>@//localhost:1521/FREEPDB1
```

### External clients

In DBeaver / DataGrip / Navicat / SQL Developer, create an Oracle connection and use the parameters above. The three common forms:

```
Service name syntax (recommended): host <server-ip>  port 1521  service name FREEPDB1
SID syntax (when the client only supports SID): host <server-ip>  port 1521  SID FREE
EZConnect: sqlplus appuser/<password>@<server-ip>:1521/FREEPDB1
```

JDBC URL:

```
jdbc:oracle:thin:@//<server-ip>:1521/FREEPDB1
```

Once connected, verify which database you are on:

```sql
select name, open_mode from v$pdbs;
select sys_context('userenv','service_name') from dual;
```

> 1Panel only maps the database port (default 1521). This app has no web console; manage the instance with a SQL client.

## Common operations

**Health status**

```bash
docker inspect --format '{{.State.Health.Status}}' oracle
docker exec oracle healthcheck.sh
```

**Reset SYS / SYSTEM password**

```bash
docker exec oracle resetPassword '<new password>'
```

**Create another application user**

```bash
docker exec oracle createAppUser <user> <password> FREEPDB1
```

**Run initialization SQL**

Create an `initdb/` directory in the app folder and put `*.sql` files there (executed in filename order). Scripts run **only on the very first database initialization**.

**Backup**

```bash
docker exec oracle expdp system/<password>@//localhost:1521/FREEPDB1 \
  directory=DATA_PUMP_DIR dumpfile=backup.dmp logfile=backup.log
docker cp oracle:/opt/oracle/admin/FREE/dpdump/backup.dmp ./
```

## Troubleshooting

**`Cannot create folder : errno=13 : Permission denied : /opt/oracle/oradata/FREE` on startup**

The image runs as the non-root `oracle` user (UID 54321), while `data/` was created as root, so the container cannot write to it. This app ships `scripts/init.sh`, which hands `data/` and `initdb/` over to UID 54321 before installation, so a normal install is not affected. If you started it manually or the directory was recreated, run:

```bash
docker compose down
sudo mkdir -p data initdb
sudo chown -R 54321:54321 data initdb
docker compose up -d
```

(If `data/` only holds leftovers from a failed initialization, `sudo rm -rf data` first and let it be recreated.)

**`ORA-00845: MEMORY_TARGET not supported`, or extremely slow startup**

Not enough host memory. The Free edition needs ~2 GB; make sure the host has 4 GB+ available and that `shm_size: "1g"` is still in the compose file.

**Logs stay at `uncompressing database data files` for a long time**

That is the first-time initialization (unpack + create database), it takes 3–8 minutes. Do not restart the container while it runs, otherwise you may end up with incomplete data files.

## Notes

- Data lives in `/opt/oracle/oradata`, mounted to `data/` in the app directory — **back it up before upgrading or reinstalling**.
- `shm_size: 1g` is set because Oracle needs adequate shared memory; do not remove it.
- `crossVersionUpdate` is `false`: Oracle does not support in-place major version upgrades. Install the new version as a separate app and migrate with expdp / impdp.
- 1Panel's "Databases" module does not manage Oracle instances; use a SQL client as described above.
- The Free edition is for development, testing and learning only. Use a licensed Oracle edition in production.

## Links

- Oracle Database Free: <https://www.oracle.com/database/free/>
- Image and usage docs: <https://github.com/gvenzl/oci-oracle-free>
- Oracle documentation: <https://docs.oracle.com/en/database/oracle/oracle-database/23/>
