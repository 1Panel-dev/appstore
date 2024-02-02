# Immich

Immich - 高性能自托管照片和视频备份解决方案

## 前提条件

### Redis 服务

Immich 使用 Redis 作为缓存服务，所以需要安装 Redis 服务。

### PostgreSQL 服务

Immich 使用 PostgreSQL 作为数据库服务，所以需要安装 PostgreSQL 服务。

+ `-db` 版本自动安装数据库服务
+ 数据库仅支持 `PostgreSQL` 数据库
    + pg版本 `14`
    + pgvecto.rs 插件版本 `v0.1.11`

#### 插件 pgvecto.rs

提供矢量相似性搜索功能。 它易于使用，并可以集成到现有的工作流程和应用程序中。
pgvecto.rs是用Rust编写的，因此与类似产品相比，它具有更好的内存安全性、更好的性能和降低的维护成本。
