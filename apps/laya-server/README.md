## 产品介绍

**Laya Server** 将[上游 Laya](https://github.com/NandhaKishorM/laya) 的 System One 推理能力封装为独立服务，提供 Jev（System One）兼容的 HTTP API 和单管理员控制台，方便开发者在自己的应用中部署和使用。

**扫码加入交流群**

<img alt="扫码加入交流群" src="https://resource.fit2cloud.com/1panel/img/wechat.png" width="150" height="150">

## 主要功能

- **System One 兼容接口**：提供 POST /v1/systemone，兼容 Jev 线协议，支持 choice、score、noul 三类决策问题批量调用。
- **内置多语言模型**：镜像内置 Laya multilingual checkpoint，自动路由 100+ 语言推理，无需额外下载模型。
- **管理控制台**：API Key 管理、Playground 调试、用量统计与接口文档，支持简体中文、English 和繁體中文。
- **安全默认**：Argon2id 管理员口令、登录限流、CSRF 与会话保护；API Key 只存 SHA-256 摘要。
