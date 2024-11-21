# Waline

**Waline** 是一款简洁、安全的评论系统。

## 参数说明

| 变量名称 | 必填 | 默认值 | 备注 |
| :------: | :---: | :---: | :--- |
| 时区<br />`Time Zone` | ✅ | `Asia/Shanghai` | 参考：[Wikipedia](https://en.wikipedia.org/wiki/List_of_tz_database_time_zones) |
| 用户登录密钥<br />`JWT Token` | ✅ | 随机生成 | 使用默认值即可 |
| 开启评论审核<br />`Comment Audit` | ✅ | `false` | 开启后评论需要经过管理员审核后才能显示 |
| 作者邮箱<br />`Author Email` |  |  | 示例: `author@example.com` | 
| 博客名称<br />`Site Name` |  |  | 示例: `My Blog Site Name` |
| 博客地址<br />`Site Url` |  |  | 示例: `https://myblog.example.com` |
| 安全域名<br />`Secure Domains` | | | 示例: `myblog.example.com`<br />配置时安全域名需要同时配置博客名称和地址<br />多个域名使用英文逗号分隔 |