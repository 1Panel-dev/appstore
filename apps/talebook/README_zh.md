# TaleBook

TaleBook 是一个基于 Calibre 的个人电子书图书馆管理系统，支持在线阅读、OPDS、多用户访问、元数据管理、图书导入、网络书源、邮件推送以及私人图书馆模式。

## 使用方法

安装完成后，在 1Panel 中打开配置的 HTTP 端口即可访问 TaleBook。该应用会将图书馆数据、设置、上传的图书、日志及生成的文件存储在挂载的 `data` 目录下。

## 注意事项

- 本应用仅供个人图书管理使用，请勿用于运营公开的网络出版或公共图书馆服务。
- 如需在容器内上传 SSL 证书，请在安装时将用户 ID 和组 ID 设置为 `0`。
- 官方项目：https://github.com/talebook/talebook
- 用户指南：https://github.com/talebook/talebook/blob/master/document/README.zh_CN.md