# SFTPGo

功能齐全、高度可配置化、支持自定义 HTTP/S，FTP/S 和 WebDAV 的 SFTP 服务。
一些存储后端支持：本地文件系统、加密本地文件系统、S3（兼容）对象存储，Google Cloud 存储，Azure Blob 存储，SFTP。

## 创建第一个管理员

开始使用 SFTPGo，你需要创建一个管理员用户：

- 通过 web 管理员界面。默认 URL 是 [http://127.0.0.1:8080/web/admin](http://127.0.0.1:8080/web/admin)

## 用户和目录管理

在启动 SFTPGo 之后，你可以管理用户和目录使用：

- [基于 Web 的管理员界面](https://github.com/drakkan/sftpgo/blob/main/docs/web-admin.md)

## 教程

一些手把手教程可以在源码文件树中的 [howto](https://github.com/drakkan/sftpgo/blob/main/docs/howto "How-to") 目录找到。

## 虚拟目录

用户 home 文件夹外或者基于不同存储提供的目录，可以作为虚拟目录进行暴露，详细信息参考 [虚拟目录](https://github.com/drakkan/sftpgo/blob/main/docs/virtual-folders.md)。

## 存储后端

### S3/GCP/Azure

每个用户可以被映射到 [S3 兼容对象存储](https://github.com/drakkan/sftpgo/blob/main/docs/s3.md) /[Google Cloud 存储](https://github.com/drakkan/sftpgo/blob/main/docs/google-cloud-storage.md)/[Azure Blob 存储](https://github.com/drakkan/sftpgo/blob/main/docs/azure-blob-storage.md) bucket 或者一个 bucket 虚拟目录，通过 SFTP/SCP/FTP/WebDAV 进行暴露。

### SFTP 后端

每个用户可以被映射到另一个 SFTP 服务器账户或者它的子目录。更多的信息可以参考 [sftpfs](https://github.com/drakkan/sftpgo/blob/main/docs/sftpfs.md)。

### 加密后端

数据静态加密通过 [cryptfs 后端](https://github.com/drakkan/sftpgo/blob/main/docs/dare.md) 进行支持。

### 其它存储后端

添加新的存储后端非常简单：

- 实现 [Fs 接口](./vfs/vfs.go#L28 "interface for filesystem backends")
- 更新用户方法 `GetFilesystem` 返回新的后端
- 更新 web 接口和 REST API CLI
- 为新的存储后端添加向 `portable` 模式添加 flags

无论如何，一些后端需要按次付费账户（或者他们提供限制期限内提供免费账户）。为了能够添加这些账户支持或者预览 PRs，请提供一个测试账户。测试账户必须在提供足够长时间维护此后端，并且支持每一次新的发行版之前做基本测试。

## 强力保护

SFTPGo 支持内置 [防护](https://github.com/drakkan/sftpgo/blob/main/docs/defender.md)。

你可以使用 [连接失败日志](https://github.com/drakkan/sftpgo/blob/main/docs/logs.md) 在诸如 [Fail2ban](http://www.fail2ban.org/) 进行工具内集成。[jails](./fail2ban/jails) 和 [filters](./fail2ban/filters) 示例，在 fail2ban 目录中与 `systemd`/`journald` 是可以同时工作的。

## 账户配置属性

关于账户配置属性的细节信息，请参考 [账户](https://github.com/drakkan/sftpgo/blob/main/docs/account.md)。

## 性能

SFTPGo 在没有特殊配置的情况下，可以实现低端硬件轻松达到 GB 量级连接，对于大多数场景足够使用了。

更多深度性能分析可以参考 [性能](https://github.com/drakkan/sftpgo/blob/main/docs/performance.md)。

## 许可证

GNU AGPL-3.0-only