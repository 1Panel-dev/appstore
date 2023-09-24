<img src="https://public-link.oss-cn-shenzhen.aliyuncs.com/mcsm_picture/logo.png" alt="MCSManagerLogo.png" width="510px" />

<br />

[![Status](https://img.shields.io/badge/npm-v6.14.15-blue.svg)](https://www.npmjs.com/)
[![Status](https://img.shields.io/badge/node-v14.17.6-blue.svg)](https://nodejs.org/en/download/)
[![Status](https://img.shields.io/badge/License-Apache%202.0-red.svg)](https://github.com/MCSManager)

[官方网站](http://mcsmanager.com/) | [使用文档](https://docs.mcsmanager.com/) | [团队主页](https://github.com/MCSManager) | [面板端项目](https://github.com/MCSManager/MCSManager) | [网页前端项目](https://github.com/MCSManager/UI) | [守护进程项目](https://github.com/MCSManager/Daemon)


中文 QQ 群：https://jq.qq.com/?_wv=1027&k=Pgl9ScGw

中文 TG 群：https://t.me/MCSManager_dev

爱发电赞助：https://afdian.net/a/mcsmanager

<br />

## 这是什么？


MCSManager 面板（简称：MCSM 面板）是一款开源，分布式，轻量级，快速部署，支持大部分游戏服务端和控制台程序的管理面板

<br />

## 软件特性

软件在 Minecraft 和其他游戏社区内中已有一定的流行程度，它可以帮助你集中管理多个物理服务器，动态在任何主机上创建游戏服务端，并且提供安全可靠的多用户权限系统，可以很轻松的帮助你管理多个服务器，一直在为 Minecraft，Terraria，Steam 游戏服务器管理员，运维人员和个人开发者提供健康的软件支持。


![截图.png](https://public-link.oss-cn-shenzhen.aliyuncs.com/mcsm_picture/MCSM9.png)

![Screenshot.png](https://mcsmanager.com/main2.png)

<img width="1322" alt="QQ20221207-174328@2x" src="https://user-images.githubusercontent.com/18360009/206144481-7f57b40d-f71b-4d7e-a617-846da69ca1a3.png">


<br />

## 运行环境

控制面板可运行在 Windows 与 Linux 平台，无需数据库与任何系统配置，只需安装 node 环境即可快速运行，属于轻量级的 Minecraft 服务端控制面板。

必须 `Node 14.17.0` 以上，无需数据库和更改任何系统配置，开箱即可运行。

<br />

## 配置/数据文件

配置文件： `data/SystemConfig/config.json`

用户数据文件：`data/User/*.json`

远程守护进程配置：`data/RemoteServiceConfig/*.json`

<br />

## 软件文档

地址：[https://docs.mcsmanager.com/](https://docs.mcsmanager.com/)

<br />

## 安装

### Linux

**点下按钮直接安装**

<br />


<br />

## 更新版本

直接使用面板的更新即可.

<br />

## 项目体系

整个软件运行需要三个项目的互相配合才可运行，您普通安装的代码是编译再整合后的产物。

[**控制面板端**](https://github.com/MCSManager/MCSManager)

- 角色：控制中心
- 责任：负责提供网页前端的后端接口，提供 API 接口，用户数据管理和对守护进程进行通信和授权。

[**网页前端**](https://github.com/MCSManager/UI)

- 角色：控制中心的用户交互界面
- 责任：以网页形式展示数据，发送请求，并且拥有与守护进程通信的能力，此项目最终产物是纯静态文件。

[**守护进程**](https://github.com/MCSManager/Daemon)

- 角色：被控端
- 责任：控制本地主机的所有实例，真实进程的实际管理者，拥有与任何对象的通信能力。

<br />

<br />

## 浏览器兼容性

- 支持 `Chrome` `Edge` `Firefox` `Safari` `Opera` 等现代主流浏览器。
- 已放弃支持 `IE` 浏览器。

<br />

## 国际化

MCSManager 已支持中文，英文两种语言，已经实现国际化全面覆盖。

软件国际化由 [Lazy](https://github.com/LazyCreeper)，[KevinLu2000](https://github.com/KevinLu2000)，[zijiren233](https://github.com/zijiren233) 和 [Unitwk](https://github.com/unitwk) 共同完成

<br />

## 贡献

如果你在使用过程中发现任何问题，可以 [提交 Issue](https://github.com/MCSManager/MCSManager/issues/new/choose) 或自行 Fork 修改后提交 Pull Request。

代码需要保持现有格式，不得格式化多余代码，具体可[参考这里](https://github.com/MCSManager/MCSManager/issues/544)。

<br />

## 问题报告

欢迎发现的任何问题进行反馈，必当及时修复。

若发现严重安全漏洞又不便公开发布，请发送邮件至: mcsmanager-dev@outlook.com。

安全问题修复后将在代码中附加漏洞发现者姓名。

<br />


## 源代码协议

源代码遵循 Apache-2.0 协议。

版权所有 2022 MCSManager 开发团队。
