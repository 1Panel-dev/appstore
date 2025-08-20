## 产品介绍

自动获得你的公网 IPv4 或 IPv6 地址，并解析到对应的域名服务。

## 主要功能

- 支持Mac、Windows、Linux系统，支持ARM、x86架构
- 支持的域名服务商 `Alidns(阿里云)` `Dnspod(腾讯云)` `Cloudflare` `华为云` `Callback` `百度云` `Porkbun` `GoDaddy` `Google Domain`
- 支持接口/网卡/[命令](https://github.com/jeessy2/ddns-go/wiki/通过命令获取IP参考)获取IP
- 支持以服务的方式运行
- 默认间隔5分钟同步一次
- 支持同时配置多个DNS服务商
- 支持多个域名同时解析
- 支持多级域名
- 网页中配置，简单又方便，默认勾选`禁止从公网访问`
- 网页中方便快速查看最近50条日志
- 支持Webhook通知
- 支持TTL
- 支持部分DNS服务商[传递自定义参数](https://github.com/jeessy2/ddns-go/wiki/传递自定义参数)，实现地域解析等功能

> **Note** 建议在启用公网访问时，使用 Nginx 等反向代理软件启用 HTTPS 访问，以保证安全性。[FAQ](https://github.com/jeessy2/ddns-go/wiki/FAQ)
