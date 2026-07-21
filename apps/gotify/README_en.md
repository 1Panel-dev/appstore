## Introduction

**Gotify** is a simple server for sending and receiving messages (real-time via WebSocket).

## Features

- Send messages via REST-API
- Receive messages via WebSocket
- Manage users, clients, and applications
- [Plugins](https://gotify.net/docs/plugin)
- Web-UI -> [./ui](https://github.com/gotify/server/tree/master/ui)
- CLI for sending messages -> [gotify/cli](https://github.com/gotify/cli)
- Android App -> [gotify/android](https://github.com/gotify/android)

## Upgrading from 2.x to 3.x

- Stop the app and back up `data` and `.env` in the app directory before upgrading.
- Gotify 3 no longer supports `config.yml`. The 1Panel template already uses environment variables; if you added a configuration file manually, migrate it with the official `migrate-config` command first.
- `GOTIFY_SERVER_SSL_LETSENCRYPT_HOSTS`, `GOTIFY_SERVER_CORS_ALLOWORIGINS`, `GOTIFY_SERVER_CORS_ALLOWMETHODS`, `GOTIFY_SERVER_CORS_ALLOWHEADERS`, and `GOTIFY_SERVER_STREAM_ALLOWEDORIGINS` now use CSV syntax. For example, change `[GET, POST]` to `GET,POST`.
- `GOTIFY_SERVER_RESPONSEHEADERS` now requires JSON. For example, change `{X-Custom-Header: "custom value"}` to `{"X-Custom-Header":"custom value"}`. The old format prevents Gotify 3 from starting.
- If the old instance uses Let's Encrypt and `GOTIFY_SERVER_SSL_LETSENCRYPT_CACHE` is still set to `certs` in `.env`, copy `/app/certs` from the old container to `data/certs` in the app directory, then change the variable to `data/certs`. This prevents certificates and ACME account data from being lost when the container is recreated.
- Gotify 3 changes token visibility, authorization for sensitive client operations, and message pagination. Check scripts and clients that use the Gotify API before upgrading. Existing application and client tokens continue to work.
- Official migration guide: https://gotify.net/docs/migrate-to-3
