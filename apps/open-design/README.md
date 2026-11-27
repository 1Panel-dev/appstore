## 产品介绍

**Open Design** 是一款开源 AI 设计工具，可通过自然语言制作原型、演示文稿和其他设计素材，并在工作区预览和编辑。

安装前用 `openssl rand -hex 32` 生成 API 令牌，并填入安装表单。浏览器弹出登录框时，用户名填写 `open-design`，密码填写同一个令牌。

应用端口默认绑定到本机。远程访问请使用经过认证的 HTTPS 反向代理或 SSH 隧道。使用域名反代时，在「允许的来源」填写浏览器访问的完整来源，例如 `https://design.example.com`。多个来源用英文逗号分隔。[官方部署文档](https://github.com/nexu-io/open-design/blob/open-design-v0.27.1/deploy/README.md)

使用 AI 生成时，可以在页面连接模型服务。使用本地 Agent 模式时，请按官方说明为容器配置对应的 CLI。[Agent CLI 配置](https://github.com/nexu-io/open-design/blob/open-design-v0.27.1/deploy/README.md#linux-mounting-host-agent-clis)
