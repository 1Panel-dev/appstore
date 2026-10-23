## Introduction

**PostGIS** is a spatial database extension for PostgreSQL that adds geographic object storage, spatial indexes, and spatial queries.

## Features

- **PostGIS included**: The default database is initialized with PostGIS, PostGIS Topology, and Tiger Geocoder extensions.
- **PostgreSQL compatible**: Uses the same user, password, port, and persistence model as PostgreSQL; existing clients can connect directly.
- **Multiple versions**: PostgreSQL 14-18 with PostGIS 3.6.
- **Persistent data**: Database files are stored under ./data.
- **Architecture**: The official images currently support amd64 only.

> Note: When upgrading from PostGIS 3.5 to 3.6, use `pg_dump` / `pg_restore`; `postgis_raster` and `postgis_sfcgal` can be enabled with `CREATE EXTENSION`.
