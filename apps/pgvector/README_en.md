## Introduction

**pgvector** is a vector similarity search extension for PostgreSQL with support for exact and approximate nearest neighbor search, HNSW/IVFFlat indexes, and multiple distance functions.

## Features

- **Vector search**: Supports L2, inner product, cosine, L1, Hamming, and Jaccard distances, plus exact and approximate nearest neighbor queries.
- **High-performance indexes**: Supports HNSW and IVFFlat indexes for different vector search scales and performance requirements.
- **PostgreSQL compatible**: Based on PostgreSQL 13-18 and works with standard PostgreSQL clients, backup, and operations tools.
- **Ready to use**: The vector extension is created automatically in the default database on first initialization.
- **Persistent data**: Database files are stored under ./data.

Note: The `vector` extension is created automatically in the default database on first initialization; existing data volumes do not rerun this step. Use `pg_dump` / `pg_restore` when migrating from an existing PostgreSQL instance.
