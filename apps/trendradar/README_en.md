## Configuration and Usage Instructions

After installing the application, please go to `application installation directory/config/config.yaml` to configure the remaining parameters.

After installing the application, please navigate to `application installation directory/config/frequency_words.txt` to configure the hot keywords you care about.

> [Keyword Configuration](https://github.com/sansan0/TrendRadar/blob/master/README-EN.md#2-keyword-configuration)

Web Service: After entering the container, execute `python manage.py start_webserver` to start the service.

## Introduction

Simple public opinion monitoring and analysis - multi-platform hotspots aggregation + AI analysis tool based on MCP.

> Does not integrate AI by default, MCP service needs to be installed separately.

## Features

- **Aggregation of Hotspots Across the Network**: By default, monitors 11 major platforms such as Zhihu, Douyin, Bilibili, etc., and additional platforms can also be added manually;
- **Intelligent Push Strategy**: Supports three push modes: daily summary, current ranking, and incremental monitoring;
- **Precise Content Filtering**: Set personal keywords, only push relevant hotspots, filter irrelevant information;
- **Trend Analysis**: Real-time tracking of news popularity changes;
- **Personalized Hotspot Algorithm**: TrendRadar's proprietary weighted algorithm;
- **Multi-channel Real-time Push**: Supports WeCom (+ WeChat), Lark, DingTalk, Telegram, email, ntfy, and other push channels;
- **AI Intelligent Analysis**: Requires additional deployment of MCP service, refer to [documentation](https://github.com/sansan0/TrendRadar#-ai-%E6%99%BA%E8%83%BD%E5%88%86%E6%9E%90)
