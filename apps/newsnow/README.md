## 产品介绍

优雅地阅读实时热门新闻

## 功能特性

- 优雅的阅读界面设计，实时获取最新热点新闻
- 支持 GitHub 登录及数据同步
- 默认缓存时长为 30 分钟，登录用户可强制刷新获取最新数据
- 根据内容源更新频率动态调整抓取间隔（最快每 2 分钟），避免频繁抓取导致 IP 被封禁
- 支持 MCP server

```json
{
  "mcpServers": {
    "newsnow": {
      "command": "npx",
      "args": ["-y", "newsnow-mcp-server"],
      "env": {
        "BASE_URL": "https://newsnow.busiyi.world"
      }
    }
  }
}
```

你可以将 `BASE_URL` 修改为你的域名。
