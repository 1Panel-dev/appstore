## Introduction

**Memoh** is an always-on, multi-member, structured long-memory, containerized AI agent platform. You can create multiple AI bots and interact with them through Telegram, Discord, Lark (Feishu), QQ, Matrix, WeCom, WeChat, Email, or the built-in Web UI.

Each bot has its own isolated container and memory system, so it can read and write files, execute commands, access the network, and connect external tools through MCP inside its own environment. It is closer to giving every bot its own computer and brain.

This package ships the recommended full stack:

- `postgres`
- `server`
- `web`
- `qdrant`
- `sparse`
- `browser`

## Highlights

- Multi-bot and multi-user conversations
- Structured long-term memory with hybrid retrieval
- Isolated bot containers for file and command operations
- MCP, skills, subagents, and browser automation support
- Web-based management and chat UI

## Before Installation

- The `server` container requires `privileged: true` and `pid: host`
- First startup may take longer than usual because the full stack initializes several services
- The recommended full stack pulls large `browser` and `sparse` images, so keep at least `8GB` of free disk space
- Two ports are exposed by default: `Web UI` uses `8082`, and `API` uses `8080`

## Access

- Web UI: `http://<host-ip>:<web-port>`
- API: `http://<host-ip>:<api-port>`

The admin credentials come from the installation form.

## Configuration

The installation scripts generate a `config.toml` file with:

- Admin username, password, and email
- JWT secret
- PostgreSQL password
- Timezone
- Optional workspace registry override

This package configures `Workspace Registry` as `docker.1ms.run` by default to speed up pulling base images for bot workspaces. If your network can access the upstream registry directly, you can adjust it as needed.
