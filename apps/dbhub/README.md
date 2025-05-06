# DBHub

**DBHub** 是一个通用数据库网关，实现了模型上下文协议 (MCP) 服务器接口。该网关允许兼容 MCP 的客户端连接并探索不同的数据库。

```bash
  +------------------+    +--------------+    +------------------+
 |                  |    |              |    |                  |
 |                  |    |              |    |                  |
 |  Claude Desktop  +--->+              +--->+    PostgreSQL    |
 |                  |    |              |    |                  |
 |      Cursor      +--->+    DBHub     +--->+    SQL Server    |
 |                  |    |              |    |                  |
 |     Other MCP    +--->+              +--->+     SQLite       |
 |      Clients     |    |              |    |                  |
 |                  |    |              +--->+     MySQL        |
 |                  |    |              |    |                  |
 |                  |    |              +--->+    MariaDB       |
 |                  |    |              |    |                  |
 +------------------+    +--------------+    +------------------+
      MCP Clients           MCP Server             Databases
```
