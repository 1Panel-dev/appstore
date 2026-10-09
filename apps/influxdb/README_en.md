## Introduction

**InfluxDB** is an open-source time-series database (TSDB) designed for high-performance storage and querying of time-series data. It is commonly used for handling monitoring data, metrics, event logs, and other types of time-series data.

## Features

- **High Performance**: Optimized for high-throughput data ingestion and fast query operations, suitable for handling large volumes of time-series data.
- **Time-Series Data Support**: Specifically designed for time-series data, providing efficient storage, querying, and analysis capabilities, including support for time functions and operations.
- **Flexible Data Model**: Uses measurements, fields, and tags for data storage, supporting complex queries and filtering.
- **SQL-Like Query Language**: Offers **InfluxQL** or **Flux** query languages with SQL-like syntax for efficient data querying and manipulation.
- **High Availability and Horizontal Scalability**: Supports distributed architecture and data replication, enhancing system reliability and scalability for large-scale data processing.

## Version Notes

- **2.x (port 8086)**: Includes a built-in Web UI and is initialized with username/password/org/bucket during installation, supporting InfluxQL and Flux.
- **3.x (InfluxDB 3 Core, port 8181)**: No built-in Web UI and uses token-based authentication. An admin token is generated automatically during installation and can be found in `data/admin-token.json` in the installation directory, with data stored under `data/<node-id>`. Use it via the HTTP API, the `influxdb3` CLI, or InfluxDB 3 Explorer / Grafana.
- The two major versions use incompatible data formats, and cross-major-version upgrades are not supported.
