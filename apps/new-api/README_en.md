## Introduction

**New API** is a next-generation LLM gateway and AI asset management system. It allows you to manage all your AI models in one application, converting various large models into a unified API format. It supports OpenAI-, Claude-, and Gemini-compatible interfaces, making it suitable for both personal use and internal enterprise distribution.

## Features

For detailed features, please refer to [Features Introduction](https://docs.newapi.pro/wiki/features-introduction)

### Core Functions

| Feature | Description |
|------|------|
| New UI | Modern user interface design |
| Multi-language | Supports Chinese, English, French, Japanese |
| Data Compatibility | Fully compatible with the original One API database |
| Data Dashboard | Visual console and statistical analysis |
| Permission Management | Token grouping, model restrictions, user management |

### Payment and Billing

- Online recharge (EPay, Stripe)
- Pay-per-use model pricing
- Cache billing support (OpenAI, Azure, DeepSeek, Claude, Qwen and all supported models)
- Flexible billing policy configuration

### Authorization and Security

- LinuxDO authorization login
- Telegram authorization login
- OIDC unified authentication

### Advanced Features

**API Format Support:**
- [OpenAI Responses](https://docs.newapi.pro/api/openai-responses)
- [OpenAI Realtime API](https://docs.newapi.pro/api/openai-realtime) (including Azure)
- [Claude Messages](https://docs.newapi.pro/api/anthropic-chat)
- [Google Gemini](https://docs.newapi.pro/api/google-gemini-chat/)
- [Rerank Models](https://docs.newapi.pro/api/jinaai-rerank) (Cohere, Jina)

**Intelligent Routing:**
- Channel weighted random
- Automatic retry on failure
- User-level model rate limiting

**Format Conversion:**
- OpenAI ⇄ Claude Messages
- OpenAI ⇄ Gemini Chat
- Thinking-to-content functionality

**Reasoning Effort Support:**

<details>
<summary>View detailed configuration</summary>

**OpenAI series models:**
- `o3-mini-high` - High reasoning effort
- `o3-mini-medium` - Medium reasoning effort
- `o3-mini-low` - Low reasoning effort
- `gpt-5-high` - High reasoning effort
- `gpt-5-medium` - Medium reasoning effort
- `gpt-5-low` - Low reasoning effort

**Claude thinking models:**
- `claude-3-7-sonnet-20250219-thinking` - Enable thinking mode

**Google Gemini series models:**
- `gemini-2.5-flash-thinking` - Enable thinking mode
- `gemini-2.5-flash-nothinking` - Disable thinking mode
- `gemini-2.5-pro-thinking` - Enable thinking mode
- `gemini-2.5-pro-thinking-128` - Enable thinking mode with thinking budget of 128 tokens

</details>
