# DBHub

**DBHub** is a universal database gateway implementing the Model Context Protocol (MCP) server interface. This gateway allows MCP-compatible clients to connect to and explore different databases.

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
