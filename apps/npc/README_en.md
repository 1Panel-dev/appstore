## Setup Notes

This submission only includes one fixed numeric version directory. Before installation, prepare the NPS server address, client connection key, and make sure the TLS setting matches the server.

NPC uses `host` network mode and runs as the NPS client. It does not provide a fixed web port. Check the 1Panel app page or container logs for runtime status.

The upstream `v0.27.01` image tag is an older build. This package uses the actively maintained `v0.26.x` image line.

## Introduction

**NPC** is the client component of NPS. It connects to an NPS server and establishes intranet penetration proxy connections based on the server-side configuration.

## Features

- **Connect to NPS server**: Connect with the server address and client key;
- **TLS communication**: Supports TLS when enabled on the server;
- **Managed by NPS**: Once connected, proxy rules can be configured and managed from the NPS web interface.

## References

- Website: <https://ehang-io.github.io/nps>
- Source: <https://github.com/yisier/nps>
