## 产品介绍

**New API** 是新一代大模型网关与 AI 资产管理系统，一个应用管理您的所有AI模型，支持将多种大模型转为统一格式调用，支持OpenAI、Claude、Gemini等格式，可供个人或者企业内部管理与分发渠道使用。

## 主要特性

详细特性请参考 [特性说明](https://docs.newapi.pro/wiki/features-introduction)

### 核心功能

| 特性 | 说明 |
|------|------|
| 全新 UI | 现代化的用户界面设计 |
| 多语言 | 支持中文、英文、法语、日语 |
| 数据兼容 | 完全兼容原版 One API 数据库 |
| 数据看板 | 可视化控制台与统计分析 |
| 权限管理 | 令牌分组、模型限制、用户管理 |

### 支付与计费

- 在线充值（易支付、Stripe）
- 模型按次数收费
- 缓存计费支持（OpenAI、Azure、DeepSeek、Claude、Qwen等所有支持的模型）
- 灵活的计费策略配置

### 授权与安全

- LinuxDO 授权登录
- Telegram 授权登录
- OIDC 统一认证
- Key 查询使用额度（配合 [neko-api-key-tool](https://github.com/Calcium-Ion/neko-api-key-tool)）

### 高级功能

**API 格式支持：**
- [OpenAI Responses](https://docs.newapi.pro/api/openai-responses)
- [OpenAI Realtime API](https://docs.newapi.pro/api/openai-realtime)（含 Azure）
- [Claude Messages](https://docs.newapi.pro/api/anthropic-chat)
- [Google Gemini](https://docs.newapi.pro/api/google-gemini-chat/)
- [Rerank 模型](https://docs.newapi.pro/api/jinaai-rerank)（Cohere、Jina）

**智能路由：**
- 渠道加权随机
- 失败自动重试
- 用户级别模型限流

**格式转换：**
- OpenAI ⇄ Claude Messages
- OpenAI ⇄ Gemini Chat
- 思考转内容功能

**Reasoning Effort 支持：**

<details>
<summary>查看详细配置</summary>

**OpenAI 系列模型：**
- `o3-mini-high` - High reasoning effort
- `o3-mini-medium` - Medium reasoning effort
- `o3-mini-low` - Low reasoning effort
- `gpt-5-high` - High reasoning effort
- `gpt-5-medium` - Medium reasoning effort
- `gpt-5-low` - Low reasoning effort

**Claude 思考模型：**
- `claude-3-7-sonnet-20250219-thinking` - 启用思考模式

**Google Gemini 系列模型：**
- `gemini-2.5-flash-thinking` - 启用思考模式
- `gemini-2.5-flash-nothinking` - 禁用思考模式
- `gemini-2.5-pro-thinking` - 启用思考模式
- `gemini-2.5-pro-thinking-128` - 启用思考模式，并设置思考预算为128tokens

</details>
