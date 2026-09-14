## Administrator Information

- On the first visit, follow the setup wizard to initialize FileCodeBox and set the administrator password.
- Admin panel: `/#/admin`
- FileCodeBox no longer ships with a default administrator password.

## Introduction

FileCodeBox is a lightweight, modern, self-hosted file sharing tool. Upload files or text without registration, receive a pickup code, and share that code with the recipient.

## Features

- [x] Lightweight stack: FastAPI + SQLite + Vue 3
- [x] Drag-and-drop, paste, batch, and chunked uploads
- [x] Unified text and file sharing
- [x] Failed-attempt protection against code brute forcing
- [x] IP-based upload rate limits
- [x] Random pickup codes with configurable expiry and retrieval limits
- [x] Chinese and English interfaces
- [x] Anonymous sharing without registration or login
- [x] Centralized administration for files, shares, storage, and security
- [x] Configurable global storage capacity limit
- [x] Built-in local, S3, and WebDAV storage, with optional OneDrive and OpenDAL extensions
- [x] Deployment through the official multi-architecture Docker image
