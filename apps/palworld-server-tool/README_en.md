# Palworld Server Management Tool

**Manage dedicated Palworld servers with a visual interface and REST API.**

Features and Roadmap Based on `Level.sav` Save File Parsing:

- [x] Complete player data
- [x] Player Pal data
- [x] Guild data

Features Based on Official RCON Commands (Server-Only):

- [x] Retrieve server information
- [x] View online player list
- [x] Kick/ban players
- [x] In-game broadcasting
- [x] Smoothly shut down the server with broadcasted messages

This tool uses **bbolt** single-file storage to periodically fetch and store data from RCON and `Level.sav` files. 

It provides a simple visual interface and REST API, making management and development easier.

When deploying with Docker, you need to configure the external save path for the Palworld server. During installation, you must provide the corresponding external save path for the Palworld server.

Ensure that private server RCON is enabled by following the instructions in the [official guide](https://github.com/zaigie/palworld-server-tool?tab=readme-ov-file#%E5%A6%82%E4%BD%95%E5%BC%80%E5%90%AF%E7%A7%81%E6%9C%8D-rcon).

---

Notes on Save File Parsing

> Parsing the `Level.sav` save file temporarily requires a certain amount of system memory (1GB~3GB) for short periods (<20s). The memory is released after the task is completed. Ensure your server has sufficient memory. If not, consider the following alternatives:

1. Deploy the tool on a separate server.
2. Use a local personal computer for deployment.
3. Opt for either of the above when the game server's performance is insufficient.

By default, the **PST tool and the game server are assumed to run on the same physical machine**. For separate deployments, refer to the following resources:

- [PST-Agent Deployment Guide](https://github.com/zaigie/palworld-server-tool/blob/main/README.agent.md)
- [Sync Save Files from k8s Pod](https://github.com/zaigie/palworld-server-tool?tab=readme-ov-file#%E4%BB%8E-k8s-pod-%E5%90%8C%E6%AD%A5%E5%AD%98%E6%A1%A3)