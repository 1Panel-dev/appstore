## 产品介绍

**magpie** 是一款把各类 AI Agent 的模型配置集中到一处的工具，通过一个兼容 OpenAI、Anthropic 与 Gemini 的本地网关为 Agent 提供模型，供应商的添加和订阅登录都在浏览器页面里完成。

## 主要功能

- **网页管理**：容器以网关模式运行，在浏览器里添加供应商、登录订阅、生成网关密钥并查看用量与路由。
- **多协议网关**：同一个地址同时支持 OpenAI Chat / Responses、Anthropic Messages 与 Gemini API，模型以 provider/model 的形式提供。
- **订阅共享与自动路由**：Claude、ChatGPT、Copilot 等订阅可以共享给其他 Agent，路由组能在额度用尽时自动切换账号或模型。
- **数据持久化**：供应商、密钥、登录、插件与用量全部保存在 /config 数据卷，重建容器后依然保留。
- **插件系统**：通过插件解锁其它能力。
