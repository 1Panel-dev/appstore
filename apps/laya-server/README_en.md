## Introduction

**Laya Server** packages the System One inference capabilities of [upstream Laya](https://github.com/NandhaKishorM/laya) as a standalone service. It provides a Jev (System One) compatible HTTP API and a single-admin console, making them easier to deploy and use in your applications.

## Features

- **System One compatible API**: Serves POST /v1/systemone on the Jev wire protocol for batched choice, score, and noul decision questions.
- **Bundled multilingual model**: Ships the Laya multilingual checkpoint and routes 100+ languages automatically, with no extra model download.
- **Admin console**: API key management, a playground, usage statistics, and built-in API docs, in Simplified Chinese, English, and Traditional Chinese.
- **Secure defaults**: Argon2id admin password, login rate limiting, CSRF and session protection; API keys are stored as SHA-256 digests only.
