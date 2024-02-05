# linkding

自己托管的书签管理器。它的设计目标是最小化、快速且易于使用。

## 功能概述：

+ 干净的用户界面优化了可读性
+ 用标签组织书签
+ 使用 Markdown 添加注释
+ 稍后阅读功能
+ 与其他用户共享书签
+ 批量编辑
+ 自动提供已添加书签的网站的标题、描述和图标
+ 自动创建 Internet Archive Wayback Machine 上添加书签的网站的快照
+ 以 Netscape HTML 格式导入和导出书签
+ [Firefox](https://addons.mozilla.org/firefox/addon/linkding-extension/)
  和 [Chrome](https://chrome.google.com/webstore/detail/linkding-extension/beakmhbijpdhipnjhnclmhgjlddhidpe) 的扩展以及小书签
+ 浅色和深色主题
+ 用于开发第三方应用程序的 REST API
+ 用于用户自助服务和原始数据访问的管理面板
+ 使用 Docker 和 SQLite 数据库轻松设置，并可选择 PostgreSQL

## 版本说明

`-db` 版本使用 PostgreSQL 数据库，默认使用 SQLite 数据库。

`-alpine` 版本使用 Alpine Linux 作为基础镜像。

## 网站图标提供商

+ 默认供应商 Google
  `https://t1.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url={url}&size=32`
+ 可选供应商 DuckDuckGo
  `https://icons.duckduckgo.com/ip3/{domain}.ico`

### 自定义供应商规则

`{url}` - 包括网站的方案和主机名，例如 https://example.com

`{domain}` - 仅包含网站的主机名，例如 example.com
