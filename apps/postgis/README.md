## 产品介绍

**PostGIS** 是基于 PostgreSQL 的空间数据库扩展，提供地理对象存储、空间索引和空间查询能力。

## 主要功能

- **内置 PostGIS**：默认数据库自动启用 PostGIS、PostGIS Topology 和 Tiger Geocoder 扩展。
- **PostgreSQL 兼容**：沿用 PostgreSQL 的用户、密码、端口和持久化配置，现有客户端可直接连接。
- **多版本支持**：提供 PostgreSQL 14–18 与 PostGIS 3.6 的组合。
- **数据持久化**：数据库文件保存在 ./data 目录。
- **架构说明**：当前官方镜像仅支持 amd64 架构。

> 注意：从 PostGIS 3.5 升级到 3.6 建议使用 `pg_dump` / `pg_restore`；`postgis_raster`、`postgis_sfcgal` 可按需 `CREATE EXTENSION`。
