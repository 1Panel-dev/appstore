## Introduction

**Moltbot** is an open-source, self-hosted personal AI assistant and multi-model gateway. It centralizes model access, sessions, and tool execution in a private environment, making it suitable for teams or individuals who need controlled AI automation.

## Highlights

- **Self-hosted**: Deploy locally or in a private environment with full data control.
- **Multi-model**: One entry point for multiple model providers and configs.
- **Extensible**: Supports tool calling and multi-channel integrations.
- **Lightweight**: Runs with Docker and fits well into 1Panel workflows.

## Common Providers & Models

- OpenAI: `openai/gpt-4o-mini`
- Anthropic: `anthropic/claude-3-7-sonnet`
- Gemini: `gemini/gemini-1.5-pro`
- Groq: `groq/llama-3.1-70b-versatile`
- Mistral: `mistral/large-latest`
- Cohere: `cohere/command-r-plus`
- MiniMax: `minimax/MiniMax-M2.1`
- Moonshot (Kimi): `moonshot/kimi-k2.5`
- Qwen: `qwen/qwen2.5-coder-32b-instruct`
- ZAI (GLM): `zai/glm-4.7`
- DeepSeek: `deepseek/deepseek-chat`



## Obtain the Moltbot Token

Open the **moltbot.json** file and copy the value of `"gateway.auth.token"`.  
This token will be used to access the Moltbot application.

```yaml
  "gateway": {
    "mode": "local",
    "auth": {
      "mode": "token",
      "token": "c9917c5a066beeb26266d09baed99495e7563b33c771e89a"
    },
    ***
```