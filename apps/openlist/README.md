## 首次安装（v4.2.7 及以后）

安装完成后，从 1Panel 的应用列表打开 OpenList 网页，按初始化向导完成环境自检，并设置管理员用户名和密码。

全新部署不再自动生成带随机密码的 `admin` 账户。尚未完成网页初始化时，不要使用下方的密码重置命令创建管理员。

## 已有安装升级

沿用原来的 `./data:/opt/openlist/data` 数据目录升级，已有管理员账户和配置会保留，无需重新初始化。请勿删除或清空 `data` 目录。

通过旧模板安装的实例需要升级到包含新模板的更高版本，才能使用下方的端口映射选项。原先手工添加过 FTP/SFTP 映射的实例，请在升级时核对 Compose 差异，保留仍需使用的映射。

## FTP / SFTP 端口映射

在 1Panel 的 OpenList 参数中，「额外端口映射」默认关闭，可选择 FTP、SFTP 或 FTP + SFTP。FTP 控制端口映射到容器的 TCP `5221`，SFTP 映射到 TCP `5222`。客户端连接时填写宿主机端口。要从其他机器连接，请在高级设置中启用端口外部访问。未指定绑定 IP 时，额外端口绑定到 `0.0.0.0`。如需 IPv6，可在高级设置中指定 IPv6 地址。

端口映射只配置 Docker 网络。请在应用安装目录的 `data/config.json` 中，将所需协议的 `ftp.enable` 或 `sftp.enable` 设为 `true`，并确认对应的 `listen` 分别为 `:5221` 或 `:5222`。

FTP 被动模式还需要数据端口。在 1Panel 里从 `1024–65535` 中选择一段空闲端口（默认 `50000-50009`），在 OpenList 管理后台的「FTP 被动传输端口映射」中填入相同范围。如果客户端使用 PASV，请在「FTP 服务端公网地址」中填写客户端可访问的服务端 IPv4 地址或域名。SFTP 只用单个端口。配置完成后重启 OpenList。[官方 FTP / SFTP 指南](https://doc.oplist.org/guide/advanced/ftp)

OpenList v4.2.10 使用本地存储驱动时，SFTP 可以登录和列目录，但文件传输可能导致 OpenList 重启。

## 忘记管理员密码

容器列表点击 `终端` 按钮，进入容器内执行命令设置密码。

- **生成随机密码**: `./openlist admin random`
- **手动设置密码**: `./openlist admin set NEW_PASSWORD`

## 产品介绍

一个支持多种存储，支持网页浏览和 WebDAV 的文件列表程序，由 gin 和 Solidjs 驱动。

## 支持的存储

支持多种网盘、对象存储、文件协议及聚合驱动，包括：

- [x] [本机存储](https://doc.oplist.org/guide/drivers/local)
- [x] [阿里云盘](https://www.alipan.com/) / [Open](https://doc.oplist.org/guide/drivers/aliyundrive_open) / [分享](https://www.alipan.com/)
- [x] [OneDrive / 分享链接](https://doc.oplist.org/guide/drivers/onedrive) / [APP](https://doc.oplist.org/guide/drivers/onedrive_app) / SharePoint（[国际版](https://www.office.com/)、[世纪互联](https://portal.partner.microsoftonline.cn)、DE、US）
- [x] [Google Drive](https://drive.google.com/)
- [x] [123 云盘 / Open / 分享 / 直链](https://www.123pan.com/)
- [x] [FTP](https://github.com/OpenListTeam/OpenList/tree/main/drivers/ftp) / [SFTP](https://github.com/OpenListTeam/OpenList/tree/main/drivers/sftp) / [SMB](https://github.com/OpenListTeam/OpenList/tree/main/drivers/smb) / [WebDAV](https://github.com/OpenListTeam/OpenList/tree/main/drivers/webdav)
- [x] [PikPak / 分享](https://www.mypikpak.com/)
- [x] [S3 兼容对象存储](https://doc.oplist.org/guide/drivers/s3)
- [x] [多吉云](https://www.dogecloud.com/product/oss)
- [x] [又拍云对象存储](https://www.upyun.com/products/file-storage)
- [x] [Azure Blob Storage](https://azure.microsoft.com/products/storage/blobs)
- [x] [Bunny Storage](https://github.com/OpenListTeam/OpenList/tree/main/drivers/bunny_storage)
- [x] Teambition（[中国](https://www.teambition.com/)、[国际](https://us.teambition.com/)）
- [x] [MediaFire](https://www.mediafire.com/)
- [x] [分秒帧](https://www.mediatrack.cn/)
- [x] [Proton Drive](https://proton.me/drive)
- [x] [天翼云盘](https://cloud.189.cn/)（个人、家庭、客户端、TV）
- [x] [中国移动云盘](https://yun.139.com/)（个人、家庭、群组、分享）
- [x] [中国联通云盘](https://pan.wo.cn/)
- [x] [四川电信魔盘](https://mopan.sc.189.cn/mopan/#/downloadPc)
- [x] [Yandex Disk](https://disk.yandex.com/)
- [x] [百度网盘](https://pan.baidu.com/)
- [x] [夸克网盘 / TV / Open](https://pan.quark.cn/)
- [x] [迅雷网盘 / X / 浏览器](https://doc.oplist.org/guide/drivers/thunder)
- [x] [蓝奏云](https://www.lanzou.com/) / [蓝奏云优享版](https://www.ilanzou.com/)
- [x] [小飞机网盘](https://feijipan.com/)
- [x] [Google 相册](https://photos.google.com/)
- [x] [MEGA](https://mega.nz/)
- [x] [一刻相册](https://photo.baidu.com/)
- [x] [TeraBox](https://www.terabox.com/)
- [x] [115 / 开放平台 / 分享](https://115.com/)
- [x] [Seafile](https://www.seafile.com/)
- [x] [Cloudreve V3 / V4](https://cloudreve.org/)
- [x] [UC 网盘 / TV](https://drive.uc.cn/)
- [x] [Dropbox](https://www.dropbox.com/)
- [x] [腾讯微云](https://www.weiyun.com/)
- [x] [超星星小组盘](https://doc.oplist.org/guide/drivers/chaoxing)
- [x] [网易云音乐云盘](https://doc.oplist.org/guide/drivers/163music)
- [x] [6 盘 / Open](https://doc.oplist.org/guide/drivers/halalcloud)
- [x] [联想家庭存储分享](https://pc.lenovo.com.cn/)
- [x] [GitHub API](https://doc.oplist.org/guide/drivers/github) / [GitHub Releases](https://doc.oplist.org/guide/drivers/github_releases)
- [x] [CNB Releases](https://cnb.cool/)
- [x] [Misskey](https://misskey-hub.net/cn/docs/for-users/features/drive/)
- [x] [Degoo](https://degoo.com/)
- [x] [豆包 / 分享](https://www.doubao.com/)
- [x] [Febbox](https://www.febbox.com/)
- [x] [OpenList / 分享 / AList V3](https://github.com/OpenListTeam/OpenList)
- [x] [Teldrive](https://github.com/tgdrive/teldrive)
- [x] [钉钉文档](https://alidocs.dingtalk.com/)
- [x] [光鸭云盘](https://doc.oplist.org/guide/drivers/guangyapan)
- [x] [KodBox](https://doc.oplist.org/guide/drivers/kodbox)
- [x] [WPS](https://doc.oplist.org/guide/drivers/wps)
- [x] [Cloudflare 图床（cloudflare_imgbed）](https://github.com/OpenListTeam/OpenList/tree/main/drivers/cloudflare_imgbed)
- [x] [Emby](https://doc.oplist.org/guide/drivers/emby)
- [x] [STRM](https://doc.oplist.org/guide/drivers/strm)
- [x] [AutoIndex](https://doc.oplist.org/guide/drivers/autoindex)
- [x] [URLTree](https://doc.oplist.org/guide/drivers/url_tree)
- [x] [IPFS API](https://github.com/OpenListTeam/OpenList/tree/main/drivers/ipfs_api)
- [x] [Crypt](https://doc.oplist.org/guide/drivers/crypt) / [别名](https://doc.oplist.org/guide/advanced/alias) / [虚拟存储](https://github.com/OpenListTeam/OpenList/tree/main/drivers/virtual) / [Chunk](https://doc.oplist.org/guide/drivers/chunk)

各驱动的配置方式与使用限制请参阅 [OpenList 官方文档](https://doc.oplist.org/guide/drivers/common)，驱动列表可查看 [官方驱动目录](https://github.com/OpenListTeam/OpenList/tree/main/drivers)。
