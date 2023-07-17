# 注意事项
 
- 运行完容器后需要获取当前数据目录 `./data/hbbs` 下的 key，方便客户端使用。

```shell
# 面板的话在文件管理里查看即可
# 终端的话输入以下获得
cat ./data/hbbs/id_ed25519.pub
```

- 如果要更改 key，请删除 `./data/hbbs` 和 `./data/hbbr` 文件夹下的 `id_ed25519` 和 `id_ed25519.pub` 文件并重新启动 hbbs/hbbr，hbbs 将会产生新的密钥对。

# RustDesk

远程桌面软件，开箱即用，无需任何配置。您完全掌控数据，不用担心安全问题。您可以使用我们的注册/中继服务器，或者[自己设置](https://rustdesk.com/server)，亦或者[开发您的版本](https://github.com/rustdesk/rustdesk-server-demo)。

## 免费的公共服务器

以下是您可以使用的、免费的、会随时更新的公共服务器列表，在国内也许网速会很慢或者无法访问。

| Location | Vendor | Specification |
| --------- | ------------- | ------------------ |
| Seoul | AWS lightsail | 1 vCPU / 0.5GB RAM |
| Germany | Hetzner | 2 vCPU / 4GB RAM |
| Germany | Codext | 4 vCPU / 8GB RAM |
| Finland (Helsinki) | 0x101 Cyber Security | 4 vCPU / 8GB RAM |
| USA (Ashburn) | 0x101 Cyber Security | 4 vCPU / 8GB RAM |

## 依赖

桌面版本界面使用[sciter](https://sciter.com/), 请自行下载。

[Windows](https://raw.githubusercontent.com/c-smile/sciter-sdk/master/bin.win/x64/sciter.dll) |
[Linux](https://raw.githubusercontent.com/c-smile/sciter-sdk/master/bin.lnx/x64/libsciter-gtk.so) |
[macOS](https://raw.githubusercontent.com/c-smile/sciter-sdk/master/bin.osx/libsciter.dylib)

移动版本使用Flutter，未来会将桌面版本从Sciter迁移到Flutter。
