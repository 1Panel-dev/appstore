## Introduction

**Remote Desktop** is a self-hosted remote desktop stack: one-click signaling, Postgres, and coturn (TURN relay) on 1Panel. Staff install the client, connect with device ID + password, and fall back to company TURN when hole punching fails.

## Features

- **One-click server**: signaling HTTP/WebSocket + Postgres + coturn
- **Primary client path**: device ID + password; account login is optional
- **Download page**: Chinese download page seeded on install, linking to public Releases
- **Public image**: `ghcr.io/jiayuexin/rd-server:<version>` pullable on any 1Panel host
