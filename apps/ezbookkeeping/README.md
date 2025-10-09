## 介绍

ezBookkeeping 是一款轻量、自托管 (self-hosted) 的个人记账应用，拥有用户友好的界面和强大的记账功能。它部署简单，借助 Docker 只需一行命令即可启动。同时对系统资源占用低、可扩展性高，既可运行在树莓派等轻量设备上，也能扩展到 NAS、MicroServer 甚至集群环境。

ezBookkeeping 为移动端和桌面端提供了各自原生的界面设计。借助 PWA (渐进式网页应用) 技术，您还可以将它 [添加到手机主屏幕](https://raw.githubusercontent.com/wiki/mayswind/ezbookkeeping/img/mobile/add_to_home_screen.gif)，像原生 App 一样使用。

## 配置文件

配置文件位于 `conf/ezbookkeeping.ini`，默认配置可在 [GitHub 仓库](https://github.com/mayswind/ezbookkeeping/blob/main/conf/ezbookkeeping.ini) 中查看。

如需自定义配置，请参考官方 [配置文档](https://ezbookkeeping.mayswind.net/zh_Hans/configuration) 获取详细说明和最佳实践。

## 相关链接

项目地址：[https://github.com/mayswind/ezbookkeeping](https://github.com/mayswind/ezbookkeeping)  
在线演示：[https://ezbookkeeping-demo.mayswind.net](https://ezbookkeeping-demo.mayswind.net)

## 特性

- **开源 & 自托管**
  - 专为隐私与数据自主而设计
- **轻量 & 快速**
  - 为性能优化，即便在资源有限的设备上也运行流畅
- **安装简单**
  - 支持 Docker
  - 支持 SQLite、MySQL、PostgreSQL 多种数据库
  - 跨平台运行 (Windows, macOS, Linux)
  - 支持 x86, amd64, ARM 架构
- **友好的用户界面**
  - 针对手机与桌面优化的 UI
  - 支持 PWA，带来接近原生 App 的使用体验
  - 深色模式
- **AI 驱动的功能**
  - 收据图片识别
  - 支持 MCP (Model Context Protocol) 用于 AI 集成
- **强大的记账功能**
  - 二级账户与分类结构
  - 支持为交易添加图片附件
  - 记录交易地理位置并在地图上展示
  - 支持周期性交易
  - 高级筛选、搜索、数据可视化与分析功能
- **本地化与国际化支持**
  - 多语言与多币种支持
  - 自动汇率更新
  - 多时区感知
  - 自定义日期、数字与货币格式
- **安全可靠**
  - 两步认证 (2FA)
  - 登录频次限制
  - 应用锁 (PIN 码 / WebAuthn)
- **数据导入/导出**
  - 支持 CSV、OFX、QFX、QIF、IIF、Camt.053、MT940、GnuCash、FireFly III、Beancount、随手记，以及支付宝、微信支付及京东金融的对账单 等多种格式

## 协议

[MIT](https://github.com/mayswind/ezbookkeeping/blob/master/LICENSE)
