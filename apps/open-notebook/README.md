## 产品介绍

**Open Notebook** 是一款开源 AI 研究笔记工具。它可以把文档、网页、音视频等来源整理到笔记本中，并用所选模型基于这些资料进行问答、生成摘要或制作播客。

安装时请使用独立的强访问密码、数据库密码和加密密钥。可运行 `openssl rand -hex 32` 生成加密密钥。升级和备份恢复时请保留原加密密钥，否则已有的模型服务凭据无法解密。

直接访问时，默认 API 端口为 `5055`。如果改了 API 端口，请把「Base URL」填为浏览器实际可访问的 API 地址。使用 HTTPS 反向代理时，可只代理 Web 端口，并填入浏览器访问的 HTTPS 根地址，不带 `/api` 后缀。[反向代理说明](https://github.com/lfnovo/open-notebook/blob/main/docs/5-CONFIGURATION/reverse-proxy.md)
