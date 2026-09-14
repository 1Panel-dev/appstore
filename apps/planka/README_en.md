## Introduction

**Planka** is an open-source, self-hosted project management tool similar to Trello, built on **Node.js, React, and PostgreSQL**.

## Features

- **Visual Boards**: Intuitive drag-and-drop operations for more efficient task management.
- **Team Collaboration**: Supports multiple users with flexible permission management.
- **Real-Time Synchronization**: WebSockets ensure real-time data updates across multiple devices.
- **Lightweight and Efficient**: Smooth operation with minimal resource usage.

## Persistence

- This app version follows the official Planka V2 persistence layout and mounts `./data` to `/app/data` inside the container.
- If you use a bind mount instead of a Docker volume, run `chown -R 1000:1000 ./data` before the first start so the default `node` user can write to the data directory.

## Upgrade Notes

- Older Planka setups usually stored files in separate directories such as `user-avatars`, `project-background-images`, and `attachments`.
- Before upgrading to this version, back up both the database and the old data directories, then migrate those files into the new `/app/data` layout by following the official Planka V2 upgrade guide. Otherwise, uploaded avatars, backgrounds, or attachments may not be carried over correctly.
- Official upgrade guide: https://docs.planka.cloud/docs/upgrade-to-v2/docker
