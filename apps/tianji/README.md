# Tianji

Tianji 是一款开源的 all-in-one 数据洞察中心。

简单的来说, `Tianji` = `Website Analytics` + `Uptime Monitor` + `Server Status`

- [官网](https://tianji.msgbyte.com/)
- [Github](https://github.com/msgbyte/tianji)

默认用户名/密码: `admin`/`admin` 请尽快修改

## 主要功能：

- **网站流量分析**：只需一段小于2kb的脚本即可监控网站的**PV/UV**, **在线时长**, **跳出率**, **访客设备使用情况**, **地理位置**，**访问来源**等等信息。可以理解为`Google Analytics`.
- **服务监控**: 实时监控服务响应时间，包括`http`、`tcp`、`ping`等多种监控手段。如果有需要更高级的方式甚至可以编写自定义脚本
- **服务状态页**: 提供给外部一个可以访问的公开页，包含了需要展示给外部看的服务状态页面。
- **服务器状态监控**: 简单来说，就是类似于`serverstatus`一样可以监控服务器当前`cpu`、`内存`、`网络流量`、`磁盘空间`等状况
- **多种通知方式**: 支持100多种常见的通知方式(主要由apprise提供)，包括但不限于`邮件`、`telegram`、`serverchan`等
- **可自定义的dashboard布局**: 支持自定义的dashboard布局。满足大量内容管理的自定义展示。
- **亮色/暗色模式**: 支持亮色暗色模式，保护使用者的眼睛。
- **多国语言支持**: Tianji 支持英法德日俄中等世界常见语言。
