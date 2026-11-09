## 产品介绍

**Oracle AI Database 23ai Free** 是 Oracle 官方提供的免费数据库版本，永久免费使用，以容器方式开箱即用。它是一个融合式数据库：关系型、JSON、图（Property Graph）、向量（AI Vector Search）等数据模型都在同一个实例里，适合开发测试、教学演示与中小规模业务系统。

本应用基于社区维护的 [`gvenzl/oracle-free`](https://github.com/gvenzl/oci-oracle-free) 镜像构建，相比 Oracle 官方 registry 镜像额外提供了：多架构支持（amd64 / arm64）、健康检查脚本、首次启动自动创建业务用户、初始化脚本目录。

## 版本说明

| 版本                  | 镜像                                          | 状态          | 说明                                                                                                               |
| --------------------- | --------------------------------------------- | ------------- | ------------------------------------------------------------------------------------------------------------------ |
| **23ai Free 23.26.3** | `gvenzl/oracle-free:23.26.3`                  | ✅ 本应用采用 | 当前活跃维护（2026-08 构建），amd64 + arm64，自带 `healthcheck.sh` 与初始化脚本机制                                |
| 21c XE 21.3.0         | `gvenzl/oracle-xe:21.3.0`                     | ⚠️ 不推荐     | 镜像自 2024-10 起未再构建，仅 amd64；21c 已不是主流版本                                                            |
| 26ai Free             | `container-registry.oracle.com/database/free` | 🔵 可关注     | 最新免费版，但镜像需从 Oracle 官方 registry 拉取，国内网络较慢且不受 Docker Hub 加速影响；无 `APP_USER` 等便利变量 |

## 免费额度限制

Oracle Free 的许可是硬限制，超限会直接报错，不能当生产数据库用：

- 最多 **2 个 CPU 线程**
- 最多 **2 GB 数据库内存**（`SGA + PGA`）
- 最多 **12 GB 用户数据**（超出报 `ORA-12954`）
- 单实例，不支持 RAC / Data Guard

因此安装时请预留至少 **2 GB 可用内存**，建议宿主机 4 GB 以上。

## 安装后信息

| 项目             | 值                                                             |
| ---------------- | -------------------------------------------------------------- |
| 端口             | 安装时填写的端口（默认 `1521`）                                |
| 服务名（PDB）    | `FREEPDB1`（如填写了「额外 PDB 名称」则为该名称）              |
| CDB 服务名 / SID | `FREE`                                                         |
| 管理员           | `SYS` / `SYSTEM`，密码为安装时填写的「SYS / SYSTEM 密码」      |
| 业务用户         | 安装时填写的「业务用户」，默认 `appuser`，创建在 `FREEPDB1` 中 |

> 首次启动需要初始化数据库，通常 **3～8 分钟**（填写了额外 PDB 会更久）。期间容器状态为 `starting`，日志出现 `DATABASE IS READY TO USE!` 后即可连接。

## 连接方式

主机填服务器 IP（容器之间互连可用容器名 `oracle`），端口为安装时填写的端口（默认 `1521`），服务名 `FREEPDB1`，SID `FREE`。

> 客户端里「服务名」和「SID」是两个不同的输入框：**服务名填 `FREEPDB1`，SID 填 `FREE`**，填反了连不上。

### 容器内 sqlplus

```bash
# 管理员
docker exec -it oracle sqlplus sys/<SYS密码>@//localhost:1521/FREEPDB1 as sysdba
# 也可以用 system
docker exec -it oracle sqlplus system/<SYS密码>@//localhost:1521/FREEPDB1
# 业务用户
docker exec -it oracle sqlplus appuser/<业务用户密码>@//localhost:1521/FREEPDB1
```

### 外部客户端

DBeaver / DataGrip / Navicat / SQL Developer 新建 Oracle 连接，按上面的参数填写即可，常见三种写法：

```
服务名语法（推荐）：主机 <服务器IP>  端口 1521  服务名 FREEPDB1
SID 语法（客户端只支持 SID 时）：主机 <服务器IP>  端口 1521  SID FREE
EZConnect 直接写：sqlplus appuser/<密码>@<服务器IP>:1521/FREEPDB1
```

JDBC 连接串：

```
jdbc:oracle:thin:@//<服务器IP>:1521/FREEPDB1
```

连上后可以用这两条语句确认连的是哪个库：

```sql
select name, open_mode from v$pdbs;
select sys_context('userenv','service_name') from dual;
```

> 1Panel 只映射了数据库端口（默认 1521），本应用没有 Web 管理界面，实例统一用 SQL 客户端管理。

## 常用操作

**查看健康状态**

```bash
docker inspect --format '{{.State.Health.Status}}' oracle
docker exec oracle healthcheck.sh
```

**修改 SYS / SYSTEM 密码**

```bash
docker exec oracle resetPassword '<新密码>'
```

**新增业务用户**

```bash
docker exec oracle createAppUser <用户名> <密码> FREEPDB1
```

**执行初始化 SQL**

在应用目录下新建 `initdb/` 并放入 `*.sql` 文件（按文件名排序执行），重启容器后生效；该目录仅在**数据库首次初始化**时执行一次。

**备份**

```bash
docker exec oracle expdp system/<密码>@//localhost:1521/FREEPDB1 \
  directory=DATA_PUMP_DIR dumpfile=backup.dmp logfile=backup.log
docker cp oracle:/opt/oracle/admin/FREE/dpdump/backup.dmp ./
```

## 常见问题

**启动时报 `Cannot create folder : errno=13 : Permission denied : /opt/oracle/oradata/FREE`**

镜像以非 root 用户 `oracle`（UID 54321）运行，而 `data/` 目录被创建成了 root 属主，容器无法写入。本应用自带 `scripts/init.sh`，安装前会自动把 `data/`、`initdb/` 授权给 54321，正常安装不会出现这个问题；如果你是手动 `docker compose up` 或目录被重建过，执行：

```bash
docker compose down
sudo mkdir -p data initdb
sudo chown -R 54321:54321 data initdb
docker compose up -d
```

（若 `data/` 里只有初始化失败的残留文件，可先 `sudo rm -rf data` 再重建。）

**启动时报 `ORA-00845: MEMORY_TARGET not supported` 或启动极慢**

宿主机内存不足。Free 版需要约 2 GB 内存，请确认宿主机可用内存 ≥ 4 GB，且 compose 里的 `shm_size: "1g"` 没有被删掉。

**日志停在 `uncompressing database data files` 很久**

首次初始化需要解压并建库，属正常现象，3～8 分钟内完成；期间不要重启容器，否则可能留下不完整的数据文件。

## 注意事项

- 数据目录为 `/opt/oracle/oradata`，已挂载到应用目录下的 `data/`，**升级或重装前请先备份该目录**。
- 容器已设置 `shm_size: 1g`，Oracle 需要足够的共享内存，请勿随意删掉该配置。
- 本应用 `crossVersionUpdate` 为 `false`：Oracle 不支持跨大版本原地升级，需要新版本请新建应用并做数据迁移（expdp / impdp）。
- 1Panel 的「数据库」模块不托管 Oracle，实例请使用上面的 SQL 客户端方式管理。
- 免费版仅用于开发、测试与学习，生产环境请使用 Oracle 商业授权版本。

## 相关链接

- 官方免费版介绍：<https://www.oracle.com/database/free/>
- 镜像与使用文档：<https://github.com/gvenzl/oci-oracle-free>
- 官方文档：<https://docs.oracle.com/en/database/oracle/oracle-database/23/>
