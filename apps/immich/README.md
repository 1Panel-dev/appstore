# Immich

Immich - 高性能自托管照片和视频备份解决方案

## 前提条件

### Redis 服务

Immich 使用 Redis 作为缓存服务，所以需要安装 Redis 服务。

### PostgreSQL 服务

Immich 使用 PostgreSQL 作为数据库服务，所以需要安装 PostgreSQL 服务。

> 插件 pgvecto.rs

提供矢量相似性搜索功能。 它易于使用，并可以集成到现有的工作流程和应用程序中。
pgvecto.rs是用Rust编写的，因此与类似产品相比，它具有更好的内存安全性、更好的性能和降低的维护成本。

## 版本说明

+ `db` 为内装数据库版本
    + 自动安装 PostgreSQL 服务
    + Redis 服务, 请自行安装
    + 不建议修改数据库用户名 `postgres`, 避免后续升级时出现权限问题
+ `version` 为 Immich 纯净版本
    + 需要自行安装 PostgreSQL 服务
    + Redis 服务, 请自行安装
    + 适合已有 PostgreSQL 服务的用户 (具体安装版本请参考官方文档说明)
    + 适合已有 Redis 服务的用户

## 升级

### 数据库 PostgreSQL

数据库为 `pgvecto-rs` 变种，无法使用官方 postgresql 数据库作为数据源，因此推荐使用 `db` 版本。
由于 1.95.1 版本开始，Immich 采用了新的数据库结构，所以需要升级数据库。

数据库镜像地址：
Docker Hub [pgvecto-rs](https://hub.docker.com/r/tensorchord/pgvecto-rs)

+ `1.94.1` 数据库版本
  tensorchord/pgvecto-rs:pg14-v0.1.11

+ `1.95.1` 数据库版本
  tensorchord/pgvecto-rs:pg14-v0.2.0

## 常见问题

+ 安装缓慢
  由于Immich官方镜像为 `ghcr.io` 仓库，国内访问速度较慢，建议使用代理或者自行构建镜像。
+ 升级失败
    + 数据库问题
      当遇到跨版本升级，`db` 版本会自动升级数据库，但是 `version` 版本需要手动升级数据库。
      `db`用户可卸载应用，并重新安装新版即可，只需要保证前后参数一致即可，数据并不会丢失。
      `version`用户需要手动升级数据库，具体升级方法请参考官方文档说明。
    + 缓存问题 Redis
      跨版本升级推荐清除Redis缓存，避免出现问题。缓存清理不会造成数据丢失。
    + 网络不存在
      由于Immich是编排模式，因此除了官方网络 `1panel-network` 之外，单独配备了其他网络，创建时应用时会提示填写网络名称。
      如果网络不存在，可在 容器 >> 网络 中创建网络删除创建的的网络即可。重新安装时会自动创建网络
