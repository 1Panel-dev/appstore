## Introduction

**Manage dedicated Palworld servers with a visual interface and REST API.**

## Features and Roadmap (Based on `Level.sav` Parsing)

- [x] Complete player data
- [x] Player Pal data
- [x] Guild data

## Features Based on Official RCON Commands (Server-Only)

- [x] Retrieve server information
- [x] View online player list
- [x] Kick/ban players
- [x] In-game broadcasting
- [x] Smoothly shut down the server with broadcasted messages

This tool uses **bbolt** databases to store application data and PST settings, and periodically fetches data from RCON and `Level.sav` files.

It provides a simple visual interface and REST API, making management and development easier.

When deploying with Docker, you need to configure the external save path for the Palworld server. During installation, you must provide the corresponding external save path for the Palworld server.

After installation, open the PST Web interface to create the administrator password, then configure `/app/save`, RCON, REST API, synchronization, and backups in PST Settings.

> **Upgrade notice**
>
> Before upgrading from version 0.12.1 or earlier, stop the Palworld Server Tool container and copy `/app/config.db`, `/app/pst.db`, and `/app/backups` to `data/config.db`, `data/pst.db`, and `data/backups` in the current application directory. Earlier 1Panel packages did not persist this data, so upgrading directly would lose PST settings, application data, and backups when the container is recreated. After upgrading, verify the save path, RCON, REST API, and other settings in the PST Web interface.

Ensure that REST API and RCON are enabled by following the instructions in the [official guide](https://github.com/zaigie/palworld-server-tool/blob/v0.12.2/docs/README.en.md#enable-rest-api-and-rcon).

---

## Notes on Save File Parsing

> Parsing the `Level.sav` save file temporarily requires a certain amount of system memory (1GB to 3GB) for short periods (<20s). The memory is released after the task is completed. Ensure your server has sufficient memory. If not, consider the following alternatives:

1. Deploy the tool on a separate server.
2. Use a local personal computer for deployment.
3. Opt for either of the above when the game server's performance is insufficient.

By default, the **PST tool and the game server are assumed to run on the same physical machine**. For separate deployments, refer to the [PST-Agent Deployment Guide](https://github.com/zaigie/palworld-server-tool/blob/v0.12.2/docs/README.agent.en.md).
