## Introduction

Pay-Unify is a unified payment platform that aggregates **Alipay, WeChat Pay and PayPal** behind a single API for payment creation, query, close and refund.

It ships an admin console for payment channel credentials and certificate management with hot reload, plus an OAuth2 Client Credentials open API for merchants with fine-grained scopes.

## Features

- **One API for all channels**: `pay / query / cancel / close / refund` across Alipay, WeChat Pay v3 and PayPal
- **Hot-reload channel configuration**: channel switches and credentials are stored in the database and take effect immediately after saving
- **Certificate lifecycle management**: upload Alipay / WeChat certificates from the console; files are persisted and hot loaded automatically
- **Admin console**: orders, users, products, projects, coin/points, membership tokens and audit logs
- **Merchant open platform**: `client_id` / `client_secret`, OAuth2 tokens, 20 fine-grained scopes, IP allowlist and rate limiting
- **Async callbacks and auto-close**: signed payment notifications and scheduled closing of expired orders
- **Security**: admin JWT session with HttpOnly cookie and CSRF double submit, login lockout and operation audit
