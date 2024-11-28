# Wakapi

**Wakapi** 是一个开源工具，可以帮助您跟踪使用不同编程语言在不同项目上进行编码所花费的时间等等。

### 客户端设置
`Wakapi` 依赖于开源 `WakaTime` 客户端工具。为了收集 `Wakapi` 的统计数据，您需要对其进行设置。

1. 为您的特定 `IDE` 或编辑器安装 `WakaTime`插件。请参阅相应的 **[插件安装指南](https://wakatime.com/plugins)**
2. 按如下方式编辑本地的 `~/.wakatime.cfg` 或者 `C:\\Users\\<user>\\.wakatime.cfg` 文件:
```cfg
[settings]
# 填写你的 Wakapi 服务地址
api_url = http://localhost:3000/api

# 填写你的 Wakapi 接口密钥 (创建账户后从网页界面获取)
api_key = 406fe41f-6d69-4183-a4cc-121e0c524c2b
```