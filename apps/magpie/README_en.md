## Introduction

**magpie** is a tool that brings every AI agent's model settings into one place, serving them through a local gateway compatible with OpenAI, Anthropic and Gemini APIs. Providers and subscription sign-ins are managed from the browser page.

## Features

- **Browser management**: The container runs in gateway mode: add providers, sign in to subscriptions, create gateway keys and review usage and routing from the browser.
- **Multi-protocol gateway**: One address serves OpenAI Chat / Responses, Anthropic Messages and Gemini APIs, with models exposed as provider/model.
- **Shared subscriptions and routing**: Claude, ChatGPT and Copilot subscriptions can be shared with other agents, and routing groups switch accounts or models when a quota runs out.
- **Persistent data**: Providers, keys, sign-ins, plugins and usage are all kept in the /config volume and survive container recreation.
- **Plugin System**: Unlock additional capabilities through plugins.
