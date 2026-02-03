## 产品介绍

**OpenClaw** 是一个运行在你自己设备上的 *个人 AI 助手*。它可以在你已经使用的各种沟通渠道中与你对话，包括：WhatsApp、Telegram、Slack、Discord、Google Chat、Signal、iMessage、Microsoft Teams、WebChat，同时还支持扩展渠道，如 BlueBubbles、Matrix、Zalo、Zalo Personal。

如果你想要一个感觉像本地助手、速度快、始终在线的单人个人助理，那就是它了。

## 核心特性

- **Local-first Gateway** — 本地优先的网关架构，统一管理会话、渠道、工具和事件的单一控制平面。
- **多渠道收件箱** — 原生支持 WhatsApp、Telegram、Slack、Discord、Google Chat、Signal、iMessage、BlueBubbles、Microsoft Teams、Matrix、Zalo、Zalo Personal、WebChat，以及 macOS、iOS / Android。
- **多 Agent 路由** — 可将不同的接入渠道 / 账号 / 对象路由到相互隔离的 Agent，实现工作区级别和 Agent 级别的会话隔离。
- **语音唤醒与对话模式** — 在 macOS / iOS / Android 上提供始终在线的语音交互能力，集成 ElevenLabs。
- **实时 Canvas** — 基于 A2UI 的可视化工作区，由 Agent 驱动，支持实时渲染和交互。
- **一等公民级工具系统** — 内置浏览器、Canvas、节点（Nodes）、定时任务（Cron）、会话管理，以及 Discord / Slack 行为操作。
- **配套客户端应用** — 提供 macOS 菜单栏应用，以及 iOS / Android 节点应用。
- **引导式上手与技能系统** — 通过向导完成初始化，内置并支持托管 / 工作区级技能（Skills）管理。

## 公共模型提供商和模型

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

## 本地 Ollama 模型配置
1. 安装 Ollama
应用商店安装 Ollama ，打开 1Panel AI-模型 菜单，添加模型
尽量使用较新的模型，推荐模型如 qwen3-coder:30b,glm-4.7,gpt-oss:20b,gpt-oss:120b
旧模型大概率不适配，会导致 OpenClaw 无法正常工作

2. 安装 OpenClaw 
API Key 填写任意英文字符即可 
Base URL 使用 `http://<ollama容器名称或者 IP 地址>:11434/v1`
模型 `ollama/<模型名称>` 例如 `ollama/qwen3:14b`

## 获取 OpenClaw Token

进入安装目录的 `data/conf` 文件夹，点击 **openclaw.json** 文件，复制其中 "gateway.auth.token" 的值，用作访问 OpenClaw 应用时的 Token。

```json
  "gateway": {
    "mode": "local",
    "auth": {
      "mode": "token",
      "token": "c9917c5a066beeb26266d09baed99495e7563b33c771e89a"
    },
  }
```

## 访问 OpenClaw 

找到 OpenClaw 应用，点击 **跳转** 按钮，在新打开的浏览器地址栏中，在 URL 后添加 `?token=你的 OpenClaw Token`。
成功进入后，即可在 Chat 页面与 OpenClaw 进行对话了。
