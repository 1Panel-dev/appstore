## Introduction

**DeepSeek Harness (dsh)** is DeepSeek's open-source agent development environment. It provides a web conversation interface, workspace file operations, command execution, tool use, and session management.

This release is a developer preview. Harness can execute commands and modify workspace files, so use a strong password and never mount the host root, Docker socket, or other sensitive directories.

## Access

Open the application at:

```text
https://SERVER_PUBLIC_IP:CONFIGURED_HTTPS_PORT
```

Set **Access Address** to the IPv4 address or hostname actually used in the browser, for example `192.168.1.10`. Do not include `https://`, a path, or a port. Caddy creates a matching internal certificate. No ACME email or host ports 80/443 are required.

Because the certificate is signed by a local CA, browsers warn on first access. Continue manually, or import this root certificate from the installation directory into the client trust store:

```text
data/caddy/pki/authorities/local/root.crt
```

After importing the root certificate, access the application through the configured address. If it changes, update the application parameter and recreate the container.

## Data directories

- `data/dsh`: Harness configuration, sessions, and user data
- `data/workspace`: Agent workspace
- `data/caddy`: Caddy local CA and certificate data

The access address may be a private IPv4 address, public IPv4 address, or hostname that resolves to the server, but it must match the address used in the browser.
