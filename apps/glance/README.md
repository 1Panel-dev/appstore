# Glance

**Glance** 是一个自托管的仪表板，它可以将所有的订阅源整合到一个界面中，让你一目了然地查看所有信息。

## 说明

安装后默认包含一个示例配置文件，其中包含了一些小部件的示例，你可以根据自己的需求修改配置文件，添加或删除小部件。
具体的配置方法请参考[配置文件说明](https://github.com/glanceapp/glance/blob/main/docs/configuration.md)、[预配置页面](https://github.com/glanceapp/glance/blob/main/docs/preconfigured-pages.md)。

## 主要功能：

- **多样化的小部件**：Glance 提供了多种小部件，包括 RSS 订阅、Subreddit 帖子、天气、书签、Hacker News、Lobsters、特定频道的最新 YouTube 视频、时钟、日历、股票、iframe、Twitch 频道和顶级游戏、GitHub 发布、代码库概览、网站监控、搜索框等；
- **主题化**：Glance 支持主题化，用户可以根据个人喜好自定义仪表板的外观；
- **移动设备优化**：Glance 针对移动设备进行了优化，无论是手机还是平板，都能提供良好的用户体验；
- **快速且轻量级**：Glance 使用最少的 JavaScript，不依赖臃肿的框架，依赖极少，单一的、易于分发的小于15MB的二进制文件，以及同样大小的 Docker 容器。所有请求都是并行化的，未缓存的页面通常在大约1秒内加载完成（取决于互联网速度和部件数量）。
