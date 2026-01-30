## Introduction

**OpenClaw** is a *personal AI assistant* you run on your own devices.
It answers you on the channels you already use (WhatsApp, Telegram, Slack, Discord, Google Chat, Signal, iMessage, Microsoft Teams, WebChat), plus extension channels like BlueBubbles, Matrix, Zalo, and Zalo Personal. It can speak and listen on macOS/iOS/Android, and can render a live Canvas you control. The Gateway is just the control plane — the product is the assistant.

If you want a personal, single-user assistant that feels local, fast, and always-on, this is it.

## Highlights

- **Local-first Gateway** — single control plane for sessions, channels, tools, and events.
- **Multi-channel inbox** — WhatsApp, Telegram, Slack, Discord, Google Chat, Signal, iMessage, BlueBubbles, Microsoft Teams, Matrix, Zalo, Zalo Personal, WebChat, macOS, iOS/Android.
- **Multi-agent routing** — route inbound channels/accounts/peers to isolated agents (workspaces + per-agent sessions).
- **Voice Wake + Talk Mode** — always-on speech for macOS/iOS/Android with ElevenLabs.
- **Live Canvas** — agent-driven visual workspace with A2UI.
- **First-class tools** — browser, canvas, nodes, cron, sessions, and Discord/Slack actions.
- **Companion apps** — macOS menu bar app + iOS/Android nodes.
- **Onboarding + skills** — wizard-driven setup with bundled/managed/workspace skills.

## Common Providers & Models

- MiniMax: `minimax/MiniMax-M2.1`
- DeepSeek: `deepseek/deepseek-chat`
- Qwen: `qwen/qwen2.5-coder-32b-instruct`
- Moonshot (Kimi): `moonshot/kimi-k2.5`
- ZAI (GLM): `zai/glm-4.7`
- OpenAI: `openai/gpt-4o-mini`
- Anthropic: `anthropic/claude-3-7-sonnet`
- Gemini: `gemini/gemini-1.5-pro`
- Groq: `groq/llama-3.1-70b-versatile`
- Mistral: `mistral/large-latest`
- Cohere: `cohere/command-r-plus`

## Obtain the OpenClaw Token

Open the **openclaw.json** file and copy the value of `"gateway.auth.token"`.  
This token will be used to access the OpenClaw application.

```yaml
  "gateway": {
    "mode": "local",
    "auth": {
      "mode": "token",
      "token": "c9917c5a066beeb26266d09baed99495e7563b33c771e89a"
    },
    ***
```
