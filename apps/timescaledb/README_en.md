## Introduction

**TimescaleDB** extends PostgreSQL for time-series workloads such as sensor readings, monitoring metrics, and trades. Hypertables partition data by time, and continuous aggregates incrementally refresh hourly or daily summaries. Both can be queried with SQL.

On first installation, the image enables the `timescaledb` extension in the default database and in `template1`. Databases created with `CREATE DATABASE` inherit the extension when they use the default template.

After upgrading the image, connect to each database that uses the extension, including `template1`, and run `ALTER EXTENSION timescaledb UPDATE;`. [Official upgrade guide](https://www.tigerdata.com/docs/deploy/self-hosted/upgrades/upgrade-docker)
