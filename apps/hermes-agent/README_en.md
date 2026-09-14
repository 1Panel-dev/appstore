## Introduction

**Hermes Agent** is a self-improving AI agent built by Nous Research. It includes a built-in learning loop that can create and improve skills from experience, keep long-term memory, search past conversations, and build a deeper model of the user across sessions.

Hermes Agent is not tied to a single local machine. It can run on a VPS, GPU cluster, or other hosted environment, and users can interact with it through the CLI, Web UI, or messaging platforms. The 1Panel package exposes the browser-based Web UI for managing configuration, API keys, runtime status, and session information.

## Features

- **Multiple model providers**: Supports Nous Portal, OpenRouter, NVIDIA NIM, Xiaomi MiMo, z.ai/GLM, Kimi/Moonshot, MiniMax, Hugging Face, OpenAI, and custom endpoints.
- **Terminal and Web UI**: Provides a terminal interface and a browser dashboard for configuration, API key management, and active session monitoring.
- **Messaging gateway**: Supports conversations through Telegram, Discord, Slack, WhatsApp, Signal, and other messaging platforms.
- **Learning and memory**: Supports agent-curated memory, past-session search, autonomous skill creation, and skill improvement during use.
- **Scheduled automations**: Includes a built-in cron scheduler for reports, backups, audits, and other unattended tasks.
- **Tools and extensions**: Provides toolsets, MCP integration, and multiple terminal backends for tool calling, automation, and remote execution workflows.
- **OpenClaw migration**: Can import settings, memories, skills, and selected secret configurations from OpenClaw.
