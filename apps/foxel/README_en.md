# Foxel

Foxel is an open-source private cloud storage solution for individuals and teams, with unified access to multiple storage backends and AI semantic search.

## Features

- Browse, upload, download, move, and share files through a unified interface.
- Connect local storage, S3, WebDAV, SFTP, FTP, SMB, and cloud drives through storage adapters.
- Preview images, audio, video, PDF, Office documents, and code in the browser.
- Manage role-based and path-level permissions, audit logs, and password-protected or expiring share links.
- Extend file management with AI semantic search, an AI assistant, plugins, and background tasks.
- Access files through WebDAV, an S3-compatible API, and temporary direct links.

## Installation

1. Set the WebUI port. The default is `8088`.
2. Keep the independently generated authentication and temporary link secrets. If setting them manually, use two different strong random values and keep them safe.
3. When using a domain or an external MCP client, set Public URL to the complete external address, such as `https://cloud.example.com`. It can be left blank for ordinary IP-based access; external MCP access requires the actual public address, including its port when applicable.
4. Open Foxel from the 1Panel application list or visit `http://SERVER_IP:WEBUI_PORT`, then follow the first-run wizard to create the administrator account.

This package uses the official image `ghcr.io/drizzletime/foxel:v2.3.4` for `amd64` and `arm64`. SQLite is used by default, so no additional database is required. AI semantic search requires configuring an embedding service and a vector database in Foxel. External services may incur costs.

## Data and upgrades

- The application's `./data` directory is mounted at `/app/data` in the container to persist the database, configuration, plugins, and local files.
- Use `/app/data/mount` when configuring a local storage adapter to keep files in the persistent directory.
- Back up the entire `data` directory before upgrading. Reuse the existing data directory and retain both secrets.
- Back up any data you wish to keep before uninstalling.

## Links

- [Website](https://foxel.cc/)
- [Project and documentation](https://github.com/DrizzleTime/Foxel)
- [Issue tracker](https://github.com/DrizzleTime/Foxel/issues)
