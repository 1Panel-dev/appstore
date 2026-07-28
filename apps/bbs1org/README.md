## 使用说明

安装完成后，访问 `http://服务器地址:端口/index.php?a=install` 完成站点初始化。

默认使用 SQLite，无需配置数据库服务。安装后可在 1Panel 的网站页面为该端口创建反向代理并申请 HTTPS 证书。

## 数据持久化

以下目录保存在应用数据目录中：

- `data`：站点配置、SQLite 数据库和运行数据
- `avatars`：用户头像
- `upload`：上传附件
- `plugins`：已安装插件

计划任务由独立的 `cron` 容器每分钟执行一次，无需额外配置 1Panel 计划任务。

## 产品介绍

**bbs1org** 是一个极简、原生 PHP 论坛，支持 SQLite、MySQL 和 PostgreSQL，包含版块、主题、回复、用户组、权限、附件和插件等社区功能。
