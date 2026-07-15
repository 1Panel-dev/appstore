## 产品介绍

**通过可视化界面及 REST 接口管理幻兽帕鲁专用服务器。**

基于 `Level.sav` 存档文件解析实现的功能及路线图：

- [x] 完整玩家数据
- [x] 玩家帕鲁数据
- [x] 公会数据

基于官方提供的 RCON 命令（仅服务器可用的）实现功能：

- [x] 获取服务器信息
- [x] 在线玩家列表
- [x] 踢出/封禁玩家
- [x] 游戏内广播
- [x] 平滑关闭服务器并广播消息

本工具使用 bbolt 分别存储业务数据与 PST 配置，将 RCON 和 Level.sav 文件的数据通过定时任务获取并保存，提供简单的可视化界面和 REST 接口，便于管理与开发。

Docker部署需要配置帕鲁服务器外部存档路径，安装时的需要填写对应的帕鲁服务器的外部存档路径。

安装完成后，首次访问 PST Web 界面创建管理密码，并在配置弹窗中设置存档目录 `/app/save`、RCON、REST API、同步及备份选项。

> **升级提示**
>
> 从 0.12.1 及更早版本升级前，请先停止 Palworld Server Tool 容器，并将容器内的 `/app/config.db`、`/app/pst.db` 和 `/app/backups` 分别备份到当前应用目录的 `data/config.db`、`data/pst.db` 和 `data/backups`。旧版 1Panel 应用包未持久化这些数据，直接升级会在容器重建后丢失 PST 配置、业务数据和备份。升级完成后，请在 PST Web 界面确认存档路径、RCON 和 REST API 等配置。

请确保前提 [开启 REST API 与 RCON](https://github.com/zaigie/palworld-server-tool/blob/v0.12.2/README.md#%E5%BC%80%E5%90%AF-rest-api-%E4%B8%8E-rcon)

> 解析 `Level.sav` 存档的任务需要在短时间（<20s）耗费一定的系统内存（1GB~3GB），这部分内存会在执行完解析任务后释放，因此你至少需要确保你的服务器有充足的内存。若不满足可使用如下等方式

这里**默认为将 pst 工具和游戏服务器放在同一台物理机上**，在一些情况下你可能不想要它们部署在同一机器上：

- 需要单独部署在其它服务器
- 只需要部署在本地个人电脑
- 游戏服务器性能较弱不满足，采用上述两种方案之一

**请参考 [pst-agent 部署教程](https://github.com/zaigie/palworld-server-tool/blob/v0.12.2/docs/README.agent.md)。**
