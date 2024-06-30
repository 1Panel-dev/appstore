![ntfy](https://github.com/binwiederhier/ntfy/raw/main/web/public/static/images/ntfy.png)

# ntfy.sh
## 通过 PUT/POST 发送推送通知到你的手机或桌面
[![Release](https://img.shields.io/github/release/binwiederhier/ntfy.svg?color=success&style=flat-square)](https://github.com/binwiederhier/ntfy/releases/latest)
[![Go Reference](https://pkg.go.dev/badge/heckel.io/ntfy.svg)](https://pkg.go.dev/heckel.io/ntfy/v2)
[![Tests](https://github.com/binwiederhier/ntfy/workflows/test/badge.svg)](https://github.com/binwiederhier/ntfy/actions)
[![Go Report Card](https://goreportcard.com/badge/github.com/binwiederhier/ntfy)](https://goreportcard.com/report/github.com/binwiederhier/ntfy)
[![codecov](https://codecov.io/gh/binwiederhier/ntfy/branch/main/graph/badge.svg?token=A597KQ463G)](https://codecov.io/gh/binwiederhier/ntfy)
[![Discord](https://img.shields.io/discord/874398661709295626?label=Discord)](https://discord.gg/cT7ECsZj9w)
[![Matrix](https://img.shields.io/matrix/ntfy:matrix.org?label=Matrix)](https://matrix.to/#/#ntfy:matrix.org)
[![Matrix space](https://img.shields.io/matrix/ntfy-space:matrix.org?label=Matrix+space)](https://matrix.to/#/#ntfy-space:matrix.org)
[![Healthcheck](https://healthchecks.io/badge/68b65976-b3b0-4102-aec9-980921/kcoEgrLY.svg)](https://ntfy.statuspage.io/)
[![Gitpod](https://img.shields.io/badge/Contribute%20with-Gitpod-908a85?logo=gitpod)](https://gitpod.io/#https://github.com/binwiederhier/ntfy)

**ntfy**（发音为 "*notify*"）是一个简单的基于 HTTP 的[发布-订阅](https://en.wikipedia.org/wiki/Publish%E2%80%93subscribe_pattern)通知服务。通过 ntfy，你可以**通过脚本从任何计算机发送通知到你的手机或桌面**，**无需注册或支付任何费用**。如果你想运行自己的服务实例，你可以很容易地做到，因为 ntfy 是开源的。

你可以通过 **[ntfy.sh](https://ntfy.sh)** 访问免费的 ntfy 服务。这里还有一个[开源的 Android 应用](https://github.com/binwiederhier/ntfy-android)，可以在[Google Play](https://play.google.com/store/apps/details?id=io.heckel.ntfy) 或 [F-Droid](https://f-droid.org/en/packages/io.heckel.ntfy/) 上找到，还有一个[开源的 iOS 应用](https://github.com/binwiederhier/ntfy-ios)，可以在[App Store](https://apps.apple.com/us/app/ntfy/id1625396347) 上找到。

## 使用说明

可以通过修改配置文件来自定义设置，文件路径如下，按需修改，将`server.yml.sample`修改为`server.yml`，

然后自定义修改内容即可。

```
/opt/1panel/apps/local/ntfy/ntfy/data/ntfy/server.yml.sample
```