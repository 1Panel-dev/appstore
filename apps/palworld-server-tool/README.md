# 幻兽帕鲁服务器管理工具

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

本工具使用 bbolt 单文件存储，将 RCON 和 Level.sav 文件的数据通过定时任务获取并保存，提供简单的可视化界面和 REST 接口和便于管理与开发。

Docker部署需要配置帕鲁服务器外部存档路径，安装时的需要填写对应的帕鲁服务器的外部存档路径。

请确保前提 [开启私服 RCON](https://github.com/zaigie/palworld-server-tool?tab=readme-ov-file#%E5%A6%82%E4%BD%95%E5%BC%80%E5%90%AF%E7%A7%81%E6%9C%8D-rcon)

> 解析 `Level.sav` 存档的任务需要在短时间（<20s）耗费一定的系统内存（1GB~3GB），这部分内存会在执行完解析任务后释放，因此你至少需要确保你的服务器有充足的内存。若不满足可使用如下等方式

这里**默认为将 pst 工具和游戏服务器放在同一台物理机上**，在一些情况下你可能不想要它们部署在同一机器上：

- 需要单独部署在其它服务器
- 只需要部署在本地个人电脑
- 游戏服务器性能较弱不满足，采用上述两种方案之一

**请参考 [pst-agent 部署教程](https://github.com/zaigie/palworld-server-tool/blob/main/README.agent.md) 或 [从 k8s-pod 同步存档](https://github.com/zaigie/palworld-server-tool?tab=readme-ov-file#%E4%BB%8E-k8s-pod-%E5%90%8C%E6%AD%A5%E5%AD%98%E6%A1%A3)**