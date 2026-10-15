## Introduction

**Silo** is a MinIO fork maintained by the [Pigsty](https://pgsty.com) community. After MinIO stopped maintaining its community edition, Silo continues to provide high-performance, Amazon S3 API-compatible object storage, making it ideal for building high-performance infrastructure for machine learning, analytics and application data workloads.

Silo is not affiliated with, endorsed or sponsored by MinIO, Inc.

## Features

- **S3 Compatible**: Fully compatible with the Amazon S3 API, so existing S3 tools and applications work out of the box with low migration cost.
- **High Performance**: Supports parallel read/write operations with low latency, suitable for workloads that need fast access to large datasets.
- **Erasure Coding**: Automatically determines the erasure-coding configuration from the number of nodes and drives, providing data redundancy for both single-node and multi-node topologies.
- **Data Security**: Supports TLS transport encryption, server-side encryption (SSE) and fine-grained access policies.
- **Multi-Tenancy & Versioning**: Supports multiple users, bucket policies, object versioning and object locking, so independent spaces can be provided for different organizations or users in a single instance.
- **Actively Maintained**: The community continuously delivers security fixes and compatibility updates, with a public security advisory ledger.

## Links

- Website: <https://silo.pgsty.com/>
- Documentation: <https://silo.pgsty.com/zh/docs/>
- Source Code: <https://github.com/pgsty/silo>
