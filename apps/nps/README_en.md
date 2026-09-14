## Setup Notes

This submission only includes one fixed numeric version directory. During installation, configure the web management port, client communication ports, HTTP/HTTPS proxy ports, web credentials, and client connection key from the form.

NPS uses `host` network mode, so its ports are bound directly on the host. Make sure the ports are available before installation and open firewall or security group rules when external access is required.

The package does not include a TLS certificate private key. To enable web HTTPS or custom HTTPS proxy certificates, place the certificate files in the installed `conf` directory and adjust the NPS certificate paths as needed.

The upstream `v0.27.01` image tag is an older build. This package uses the actively maintained `v0.26.x` image line.

## Introduction

**NPS** is a lightweight, high-performance intranet penetration proxy server. It supports TCP, UDP, HTTP, HTTPS, SOCKS5, and other proxy modes for exposing internal services to external networks.

## Features

- **Multiple proxy protocols**: Supports TCP, UDP, HTTP, HTTPS, SOCKS5, and more;
- **Web management UI**: Manage clients, tunnels, and host proxy rules from the web interface;
- **Client key authentication**: NPC clients can connect with the key configured on the server;
- **TLS communication**: Supports TLS for server-client communication.

## References

- Website: <https://ehang-io.github.io/nps>
- Source: <https://github.com/yisier/nps>
