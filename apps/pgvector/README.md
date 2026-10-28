## 产品介绍

**pgvector** 是基于 PostgreSQL 的向量相似度搜索扩展，支持精确/近似最近邻搜索、HNSW/IVFFlat 索引以及多种距离函数。

## 主要功能

- **向量搜索**：支持 L2、内积、余弦、L1、Hamming 和 Jaccard 距离，以及精确和近似最近邻查询。
- **高性能索引**：支持 HNSW 和 IVFFlat 索引，适合不同规模和性能要求的向量检索场景。
- **PostgreSQL 兼容**：基于 PostgreSQL 13–18，可使用标准 PostgreSQL 客户端、备份和运维工具。
- **开箱即用**：首次初始化时自动在默认数据库创建 vector 扩展。
- **数据持久化**：数据库文件保存在 ./data 目录。

注意：首次初始化会自动在默认数据库创建 `vector` 扩展；已有数据卷不会重复执行。从现有 PostgreSQL 迁移请使用 `pg_dump` / `pg_restore`。
