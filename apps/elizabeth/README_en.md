## Introduction

**Elizabeth** is a self-hosted real-time file and message sharing tool that
requires no account registration. Users share Markdown messages, files, images,
and links through rooms, while one Rust container serves the API, WebSocket
endpoint, and embedded web interface.

## Features

- **Real-time rooms**: Synchronizes messages and room state over WebSocket and
  supports shareable room links.
- **File and message sharing**: Supports Markdown, code highlighting, images,
  PDFs, text files, and link previews.
- **Security and permissions**: Provides JWT sessions, room passwords, granular
  permissions, expiry policies, and entry limits.
- **Lightweight deployment**: Runs as a single container with SQLite by default
  and can connect to an external PostgreSQL database.
