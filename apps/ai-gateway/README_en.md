## Introduction

**1Panel AI Gateway** is an enterprise AI access and governance platform. It provides model proxying, intelligent routing, permission control, content compliance, and usage analytics so applications can use different model resources securely, reliably, and efficiently.

Applications only need a unified Base URL and API key to access public cloud APIs, local models, and other model services through standard interfaces.

## Features

- **Unified model access**: Connect providers and local models, then manage them through model mappings and upstream account pools.
- **Users and permissions**: Control model permissions, concurrency limits, and token quotas by user group, user, and API key.
- **Intelligent routing**: Route requests to appropriate model groups based on request content and configured rules.
- **Load and concurrency control**: Select upstream accounts using priority, real-time load, and health status.
- **Content compliance and review**: Support sensitive-word checks, semantic review, and request-content auditing.
- **Usage analytics**: Track request volume, token consumption, cache utilization, and response latency.

## Database variants

- `1.4.0`: SQLite for single-host deployments. Existing SQLite installations can upgrade along this version line.
- `1.4.0-postgres`: PostgreSQL, requiring 1Panel v2.4.0 or later and PostgreSQL 18. Select a PostgreSQL service configured in 1Panel; the panel creates and associates the application database.
- Both variants use `1panel/ai-gateway:v1.4.0` with different database settings. The application directory contains persistent configuration and other files; PostgreSQL data resides in the selected database service. Use 1Panel application backups to include the associated database.
- Direct upgrades and cross-type backup restores between SQLite and PostgreSQL are not supported. Switching database types requires a separate data migration.
