## 产品介绍

**TimescaleDB** 是 PostgreSQL 的时序扩展，可用标准 SQL 查询传感器读数、监控指标和交易记录。超表按时间自动分区，连续聚合可以增量更新按小时或按天汇总的结果。

首次安装时，镜像会在默认数据库和 `template1` 中启用 `timescaledb` 扩展。之后用 `CREATE DATABASE` 按默认模板创建的数据库也会继承此扩展。

升级镜像后，请连接到每个已启用扩展的数据库（包括 `template1`），执行 `ALTER EXTENSION timescaledb UPDATE;`。[官方升级说明](https://www.tigerdata.com/docs/deploy/self-hosted/upgrades/upgrade-docker)
