# AList

A file list program that supports multiple storage, and supports web browsing and webdav, powered by gin and Solidjs.

## Supported Storage

- Local storage
- [Crypt](/guide/drivers/Crypt.md)
- [Aliyundrive Open](../guide/drivers/aliyundrive_open.md)
- [aliyundrive](https://www.alipan.com/)
- [OneDrive](./drivers/onedrive.md) /[APP](./drivers/onedrive_app.md)/ Sharepoint ([global](https://www.office.com/), [cn](https://portal.partner.microsoftonline.cn),de,us）
- [GoogleDrive](https://drive.google.com/)
- [123pan/Share/Link](https://www.123pan.com/)
- [Alist](https://github.com/Xhofe/alist)
- FTP
- SFTP
- [PikPak / share](https://www.mypikpak.com/)
- [S3](../guide/drivers/s3.md)
- [Doge](../guide/drivers/s3.md#add-object-storage-examples-and-official-documents)
- [UPYUN Storage Service](https://www.upyun.com/products/file-storage)
- WebDAV
- Teambition（[China](https://www.teambition.com/)，[International](https://us.teambition.com/)）
- [mediatrack](https://www.mediatrack.cn/)
- [189cloud](https://cloud.189.cn) (Personal, Family)
- [139yun](https://yun.139.com/) (Personal, Family)
- [Wopan](https://pan.wo.cn)
- [MoPan](https://mopan.sc.189.cn/mopan/#/downloadPc)
- [YandexDisk](https://disk.yandex.com/)
- [BaiduNetdisk](https://pan.baidu.com/) / [share](./drivers/baidu_share.md)
- [Quark/TV](https://pan.quark.cn/)
- [Thunder / X Browser](../guide/drivers/thunder.md)
- [Lanzou](https://www.lanzou.com/)、[NewLanzou](https://www.ilanzou.com)
- [Feiji Cloud](https://feijipan.com/)
- [Aliyundrive share](https://www.alipan.com/)
- [Google photo](https://photos.google.com/)
- [Mega.nz](https://mega.nz)
- [Baidu photo](https://photo.baidu.com/)
- [TeraBox](https://www.terabox.com/)
- [AList v2/v3](../guide/drivers/Alist%20V2%20V3.md)
- SMB
- [alias](../guide/advanced/alias.md)
- [115](https://115.com/)
- [Seafile](https://www.seafile.com/)
- Cloudreve
- [Trainbit](https://trainbit.com/)
- [UrlTree](../guide/drivers/UrlTree.md)
- IPFS
- [UC Clouddrive/TV](https://drive.uc.cn/)
- [Dropbox](https://www.dropbox.com)
- [Tencent weiyun](https://www.weiyun.com/)
- [vtencent](https://app.v.tencent.com/)
- [ChaoxingGroupCloud](../guide/drivers/chaoxing.md)
- [Quqi Cloud](https://quqi.com)
- [163 Music Drive](../guide/drivers/163music.md)
- [halalcloud](../guide/drivers/halalcloud.md)
- [LenovoNasShare](https://pc.lenovo.com.cn)

## Account Password

Click the `Terminal` button in the container list to enter the container and execute commands to set the password.

- **Use a random password**: `./alist admin random`
- **Or set password manually**: `./alist admin set NEW_PASSWORD`

