
## 配置和使用说明

安装应用后请前往 `应用安装目录/config/config.yaml` 配置剩余参数。

安装应用后请前往 `应用安装目录/config/frequency_words.txt` 配置你关心的热点词汇。

> [关键词配置](https://github.com/sansan0/TrendRadar#2-%E5%85%B3%E9%94%AE%E8%AF%8D%E9%85%8D%E7%BD%AE)

Web 服务: 进入容器后，执行 `python manage.py start_webserver` 启动服务。

## 产品介绍

简单的舆情监控分析 - 多平台热点聚合+基于 MCP 的AI分析工具。

> 本身不集成 AI, 需要额外安装MCP服务。

## 主要功能

- **全网热点聚合**: 默认监控知乎、抖音、B站等 11 个个主流平台，也可自行增加额外的平台；
- **智能推送策略**: 支持当日汇总、当前榜单、增量监控等三种推送模式；
- **精准内容筛选**: 设置个人关键词，只推送相关热点，过滤无关信息；
- **热点趋势分析**: 实时追踪新闻热度变化；
- **个性化热点算法**: TrendRadar 特有权重算法；
- **多渠道实时推送**: 支持企微(+ 微信)、飞书、钉钉、Telegram、邮件、ntfy 等推送渠道；
- **AI 智能分析**: 需额外部署 MCP 服务，参考[文档](https://github.com/sansan0/TrendRadar#-ai-%E6%99%BA%E8%83%BD%E5%88%86%E6%9E%90)
