# docker-qbittorrent

docker 版本的种子下载工具 qbittorrent

## 应用初始化：

WebUI 位于 `<your-ip>:<port>` ，初始用户 `admin` 的临时密码将在启动时打印到容器日志中。

然后，您必须在设置的 Web UI 部分更改用户名/密码。如果不更改密码，每次启动容器时都会生成一个新密码。

如果您运行的是非常旧的(3.x)内核，可能会遇到[这个问题](https://github.com/linuxserver/docker-qbittorrent/issues/103)，可以用[这个方法](https://github.com/linuxserver/docker-qbittorrent/issues/103#issuecomment-831238484)解决。

## [LinuxServer.io](https://linuxserver.io/)

LinuxServer.io团队为您带来了另一个以容器为特色的版本：

- 定期及时更新应用程序
- 方便用户映射（PGID、PUID）
- 带有 S6 叠加功能的自定义基本映像
- 每周更新基础操作系统，在整个 LinuxServer.io 生态系统中使用通用层，最大限度地减少空间使用、停机时间和带宽
- 定期安全更新

Find us at: 找到我们

- [Blog](https://blog.linuxserver.io/) - 您可以用我们的容器做的所有事情，包括操作指南、意见等！
- [Discord](https://discord.gg/YWrKVTn) - 与社区和团队进行实时支持/聊天。
- [Discourse](https://discourse.linuxserver.io/) - 在我们的社区论坛上发帖。
- [Fleet](https://fleet.linuxserver.io/) - 在线网络界面，显示我们维护的所有映像。
- [GitHub](https://github.com/linuxserver) - 查看我们所有资源库的源代码。
- [Open Collective](https://opencollective.com/linuxserver) - 请考虑通过捐赠或为我们的预算捐款来帮助我们