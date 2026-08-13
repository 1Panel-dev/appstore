## Installation

### V4

1. Ensure that the server runs Docker Engine 24 or newer with Docker Compose v2 and has at least 2 GB of memory. Select a PostgreSQL 14–18 service, then enter the public HTTPS domain planned for ZITADEL.
2. After installation, create a reverse-proxy website in 1Panel, set its target to `http://127.0.0.1:<application HTTP port>`, and enable HTTPS. 1Panel enables HTTP/2 for an HTTPS website.
3. Remove the default generated `location /` from the website configuration and add the following configuration, following ZITADEL's official NGINX example. A regular HTTP/1.1 `proxy_pass` does not support ZITADEL's native gRPC API.

```nginx
location / {
    grpc_pass grpc://127.0.0.1:<application HTTP port>;
    grpc_set_header Host $host;
    grpc_set_header X-Forwarded-Proto https;
}
```

4. Sign in with the initial organization administrator created during installation. With the default username `zitadel-admin`, the full login name is `zitadel-admin@zitadel.<public-domain>`. Initial administrator settings are applied only during the first initialization.

The V4 package uses Login V1, which is built into the main ZITADEL binary and remains officially supported, so it runs only one ZITADEL container. If Login V2 or features depending on it are required, plan a separate deployment by following the official [Login V2 adoption guide](https://zitadel.com/docs/self-hosting/manage/adopt-login-v2). See also the official ZITADEL [NGINX reverse-proxy guide](https://zitadel.com/docs/self-hosting/manage/reverseproxy/nginx) and [system requirements](https://zitadel.com/docs/self-hosting/manage/requirements).

## Notes

- The V4 package initializes ZITADEL for `https://<public-domain>:443`. The domain entered during installation must exactly match the URL used by users, or ZITADEL will return `Instance not found`. Do not access it directly through the application port.
- Confirm the public domain and initial administrator settings before installation; they cannot be changed later through 1Panel application parameters after the first initialization.
- The master key must contain exactly 32 characters. Before the first start, the installation script generates it securely and stores it in `data/masterkey.env`; the file is included in application backups and must not be changed or lost after first initialization.
- The V4 package explicitly disables instance-wide Login V2 and continues using Login V1, which is built into the main V4 binary and remains officially supported. This setting is applied only during first initialization.
- V4 is a separate version and does not support a direct one-click upgrade from the App Store's existing V3.3.2 package. Before migrating an existing instance, back up the database, first upgrade to the latest V3.4.x release (at least V3.4.1), wait for legacy tokens and sessions to expire or plan a user re-login, then run V4 `setup` to apply migrations before starting V4, as described in the [official V3-to-V4 upgrade guide](https://zitadel.com/docs/self-hosting/manage/upgrade-v3-to-v4).
- V2 and V3 likewise need to be accessed through an HTTPS reverse proxy using the domain configured during installation.

## Introduction

**ZITADEL** is open-source identity infrastructure providing authentication, authorization, multi-tenancy, and auditing for B2B and B2C use cases.

## Features

- OpenID Connect, OAuth 2.x, and SAML 2.0 support.
- Passkeys, multi-factor authentication, and passwordless sign-in.
- Multi-tenant organizations, projects, roles, and authorization management.
- Management console, self-service login, APIs, and audit events.
