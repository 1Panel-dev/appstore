## 产品介绍

**SearXNG** 是一款开源元搜索引擎，可在一个页面汇总多个搜索服务的结果。自建实例可以按需选择搜索来源。

首次启动时，应用会生成 `config/settings.yml` 并写入默认配置和随机密钥。图标缓存保存在 `data` 目录。

如需调用 JSON 搜索 API，请在 `config/settings.yml` 的 `search.formats` 中加入 `json` 并保留 `html`，然后重启应用。[搜索 API 文档](https://docs.searxng.org/dev/search_api.html)
