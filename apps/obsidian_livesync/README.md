## 使用说明

CouchDB 部署成功后, 需要手动创建一个数据库, 方便插件连接并同步.

访问 `http://localhost:5984/_utils`, 输入帐号密码后进入管理页面，点击 `Create Database` 根据个人喜好创建数据库

> 如果你想要从移动设备访问 Self-hosted LiveSync，你需要一个合法的 SSL 证书。

## 产品介绍

**Self-hosted LiveSync** (自搭建在线同步) 是一个社区实现的在线同步插件。使用一个自搭建的或者购买的 CouchDB 作为中转服务器，兼容所有支持 Obsidian 的平台。

> 本插件与官方的 `Obsidian Sync` 服务不兼容。

## 主要功能

-   可视化的冲突解决器
-   接近实时的多设备双向同步
-   可使用 CouchDB 以及兼容的服务，如 IBM Cloudant
-   支持端到端加密
-   插件同步 (Beta)
-   从 [obsidian-livesync-webclip](https://chrome.google.com/webstore/detail/obsidian-livesync-webclip/jfpaflmpckblieefkegjncjoceapakdf) 接收 WebClip (本功能不适用端到端加密)

适用于出于安全原因需要将笔记完全自托管的研究人员、工程师或开发人员，以及任何喜欢笔记完全私密所带来的安全感的人。

> 具体使用问题，仍需参考官方文档
