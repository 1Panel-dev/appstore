<div align="center">
<br>
<img width="200" src="https://raw.githubusercontent.com/cc63/ICON/main/Sub-Store.png" alt="Sub-Store">
<br>
<br>
<h2 align="center">Sub-Store<h2>
</div>

<p align="center" color="#6a737d">
Advanced Subscription Manager for QX, Loon, Surge, Stash and Shadowrocket.
</p>

[![Build](https://github.com/sub-store-org/Sub-Store/actions/workflows/main.yml/badge.svg)](https://github.com/sub-store-org/Sub-Store/actions/workflows/main.yml) ![GitHub](https://img.shields.io/github/license/sub-store-org/Sub-Store) ![GitHub issues](https://img.shields.io/github/issues/sub-store-org/Sub-Store) ![GitHub closed pull requests](https://img.shields.io/github/issues-pr-closed-raw/Peng-Ym/Sub-Store) ![Lines of code](https://img.shields.io/tokei/lines/github/sub-store-org/Sub-Store) ![Size](https://img.shields.io/github/languages/code-size/sub-store-org/Sub-Store) 
<a href="https://trendshift.io/repositories/4572" target="_blank"><img src="https://trendshift.io/api/badge/repositories/4572" alt="sub-store-org%2FSub-Store | Trendshift" style="width: 250px; height: 55px;" width="250" height="55"/></a>
[!["Buy Me A Coffee"](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.buymeacoffee.com/PengYM)
   
Core functionalities:

1. Conversion among various formats.
2. Subscription formatting.
3. Collect multiple subscriptions in one URL.


***
## 镜像地址

- https://hub.docker.com/r/xream/sub-store

## 使用示例

### 一键配置打开 本地前端+本地后端

[`http://127.0.0.1:3001?api=http://127.0.0.1:3001/2cXaAxRGfddmGz2yx1wA`](http://127.0.0.1:3001/?api=http://127.0.0.1:3001/2cXaAxRGfddmGz2yx1wA)

意思是 后端地址为 `http://127.0.0.1:3001/2cXaAxRGfddmGz2yx1wA`

简单验证一下 `http://127.0.0.1:3001/2cXaAxRGfddmGz2yx1wA/api/utils/env` 可以看到版本信息

同样此 URL 也可以作为健康检查的 URL

## 启动

数据文件夹: `/root/sub-store-data`

端口: `3001`

监听: `127.0.0.1` // 本示例中演示的是本地版, 局域网直接访问等场景请自己设置

后端前缀: `/2cXaAxRGfddmGz2yx1wA`

定时任务: `55 23 * * *` 每天 23 点 55 分(避开部分机场后端每天0点定时重启)

> 本示例中演示的定时任务环境变量为 `SUB_STORE_CRON`, 此时使用的是系统的 `crond`; 如果有问题, 可以使用 `SUB_STORE_BACKEND_CRON`, 此时将使用 Node 版 `node-cron`

推送服务: `https://api.day.app/XXXXXXXXXXXX/[推送标题]/[推送内容]?group=SubStore&autoCopy=1&isArchive=1&sound=shake&level=timeSensitive&icon=https%3A%2F%2Fraw.githubusercontent.com%2F58xinian%2Ficon%2Fmaster%2FSub-Store1.png`

> 支持 Bark/PushPlus 等服务. 形如: `https://api.day.app/XXXXXXXXX/[推送标题]/[推送内容]?group=SubStore&autoCopy=1&isArchive=1&sound=shake&level=timeSensitive` 或 `http://www.pushplus.plus/send?token=XXXXXXXXX&title=[推送标题]&content=[推送内容]&channel=wechat` 的 URL, `[推送标题]` 和 `[推送内容]` 会被自动替换.

```
docker run -it -d --restart=always -e "SUB_STORE_PUSH_SERVICE=https://api.day.app/XXXXXXXXXXXX/[推送标题]/[推送内容]?group=SubStore&autoCopy=1&isArchive=1&sound=shake&level=timeSensitive&icon=https%3A%2F%2Fraw.githubusercontent.com%2F58xinian%2Ficon%2Fmaster%2FSub-Store1.png"  -e "SUB_STORE_CRON=55 23 * * *" -e SUB_STORE_FRONTEND_BACKEND_PATH=/2cXaAxRGfddmGz2yx1wA -p 127.0.0.1:3001:3001 -v /root/sub-store-data:/opt/app/data --name sub-store xream/sub-store
```