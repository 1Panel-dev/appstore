## 产品介绍

**Cloudflared** 是 Cloudflare 提供的一个工具，用于创建安全的连接，并允许将私有网络和服务暴露到公共互联网。

## 如何使用

### 1. **安装和设置：**

- Cloudflared 是一个连接应用程序的工具，可用于将私有服务和网络连接到Cloudflare的网络。你可以在 [Cloudflare的官方文档](https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/) 上找到安装和设置的详细步骤。

### 2. **运行Cloudflared容器：**

- 使用 Docker 运行 Cloudflared 容器的示例命令如下：

   ```shell
   docker run cloudflare/cloudflared:latest tunnel --no-autoupdate --hello-world
   ```

   > 此示例使用`--hello-world`参数，依赖于trycloudflare.com，不需要Cloudflare帐户。这是为了快速入门而设计的单一命令。

### 3. **实际用途：**

- 对于实际用途，建议创建一个免费的 Cloudflare 帐户，并在 [Cloudflare控制台](https://dash.teams.cloudflare.com/) 的Access -> Tunnels 部分创建隧道。在那里，你将获得一个用于启动和运行 cloudflared Docker 容器的单行命令，并且需要通过 Cloudflare 帐户进行身份验证。

### 4. **用途：**

- Cloudflared 可用于将私有HTTP服务暴露到公共DNS主机名，并可以选择通过 Cloudflare Access 进行访问控制。
- 还可以使用 Cloudflared 连接私有网络，允许 WARP 注册用户通过 TCP/UDP IP/port 访问，实现 Zero Trust 安全策略，避免使用传统的 VPN 。

> 请注意，具体的操作步骤可能因 Cloudflare 的更新而有所变化，建议查阅官方文档以获取最新信息。
