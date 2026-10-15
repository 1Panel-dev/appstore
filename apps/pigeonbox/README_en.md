## Introduction

**PigeonBox** is an open-source, self-hosted platform for anonymous, passcode-based sharing of text and files — like a parcel locker for your data. Each share produces a 6-digit pickup code that anyone can redeem in a browser, with expiration and auto-cleanup, password protection, multi-user management, built-in MCP AI integration and P2P device direct transfer.

## Features

- **Anonymous Passcode Sharing**: Share text or files with a pickup code; password protection and automatic expiry cleanup included.
- **Multi-user & API Tokens**: User registration/login/ban management; personal access tokens (pb_sk_ prefix) let scripts and CI call the API directly.
- **14 Storage Backends**: Hot-swappable backends: local / S3 / Alibaba OSS / Tencent COS / Baidu BOS / Kingsoft KS3 / Huawei OBS / WebDAV / FTP / SFTP / GCS / Azure Blob / HDFS / OneDrive.
- **Chunked & Instant Upload**: Large files upload in chunks; the server computes SHA-256 while streaming, so identical files are shared instantly.
- **S3 Presigned Direct Upload**: Browsers upload straight to object storage via presigned URLs without consuming app bandwidth.
- **MCP AI Integration**: Built-in Model Context Protocol server so AI clients (e.g. Claude) can create/query/clean file shares.
- **P2P Federation & Device Direct Transfer**: Every server is a symmetric P2P node; pickup codes work across federated nodes while files stream from the source; p2pc end-to-end encrypted device-to-device transfer.
- **OIDC Single Sign-On**: Connect any OIDC IdP and issue local JWTs; content moderation and SMTP notifications built in.
