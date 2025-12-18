## Introduction

Elegant reading of real-time and hottest news

## Features

- Clean and elegant UI design for optimal reading experience
- Real-time updates on trending news
- GitHub OAuth login with data synchronization
- 30-minute default cache duration (logged-in users can force refresh)
- Adaptive scraping interval (minimum 2 minutes) based on source update frequency to optimize resource usage and prevent IP bans
- support MCP server

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

You can change the `BASE_URL` to your own domain.
