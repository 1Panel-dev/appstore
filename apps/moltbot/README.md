## 产品介绍

**Moltbot** 是一个开源、自托管的个人 AI 助手与多模型网关，支持在本地或私有环境中集中管理模型、会话与工具调用，适合团队或个人进行可控的 AI 自动化部署。

## 产品特色

- **自托管**：本地或私有环境部署，数据可控；
- **多模型**：统一入口管理多家模型与配置；
- **可扩展**：支持工具调用与多渠道接入；
- **轻量部署**：Docker 即可运行，适配 1Panel 管理流程。

## 常见模型提供商与示例模型

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


## 获取 Moltbot Token

进入安装目录的 `data/conf` 文件夹，
点击 **moltbot.json** 文件，复制其中 "gateway.auth.token" 的值，用作访问 Moltbot 应用时的 Token。

```yaml
```json
  "gateway": {
    "mode": "local",
    "auth": {
      "mode": "token",
      "token": "c9917c5a066beeb26266d09baed99495e7563b33c771e89a"
    },
    ***
```