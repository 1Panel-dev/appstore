## 使用说明

### 1. 安装进入安装目录

1. 
进入已安装应用页面，找到 **Moltbot 应用**，点击顶部的 **进入安装目录** 按钮。

### 2. 初始化 Moltbot

点击文件列表顶部的 **终端** 按钮，执行初始化命令：

```bash
docker compose -f docker-compose-cli.yml run --rm moltbot-cli onboard
```

按提示完成 Moltbot 的初始化配置。

### 3. 修改配置文件

1. 初始化完成后，进入 `data/conf` 目录，编辑 **clawdbot.json** 文件。
2. 新增 `gateway.controlUi.allowInsecureAuth` 配置：

```yaml
  "gateway": {
    ***
    },
    "controlUi": {
      "allowInsecureAuth": true
    }
  },
```

### 4. 获取 Moltbot Token

点击 **clawdbot.json** 文件，复制其中 "gateway.auth.token" 的值，用作访问 Moltbot 应用时的 Token。

```yaml
  "gateway": {
    "mode": "local",
    "auth": {
      "mode": "token",
      "token": "c9917c5a066beeb26266d09baed99495e7563b33c771e89a"
    },
    ***
```

### 5. 重建应用并访问

1. 返回 **已安装应用页面**，找到 Moltbot 应用，点击 **重建** 按钮。  
2. 等待安装完成后，点击跳转按钮，在新打开的浏览器地址栏中，在 URL 后添加 `?token=你的Moltbot Token`，例如：

```
http://your-domain-or-ip:端口/?token=c9917c5a066beeb26266d09baed99495e7563b33c771e89a
```
