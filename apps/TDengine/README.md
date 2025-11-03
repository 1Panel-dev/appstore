![](https://fastly.jsdelivr.net/gh/taosdata/TDengine@main/docs/assets/TDengine-logo-trans.png)

# TDengine

TDengine 是一款开源、高性能、云原生、AI 驱动的时序数据库 (Time-Series Database, TSDB)。

了解TDengine高级功能的完整列表，请 [点击](https://tdengine.com/tdengine/)。体验 TDengine 最简单的方式是通过 [TDengine云平台](https://cloud.tdengine.com/)。对最新发布的 TDengine 组件 TDgpt，请访问 [TDgpt README](https://github.com/taosdata/TDengine/blob/main/tools/tdgpt/README.md) 了解细节。

关于完整的使用手册，系统架构和更多细节，请参考 [TDengine](https://www.taosdata.com/) 或者 [TDengine 官方文档](https://docs.taosdata.com/)。

打开浏览器，访问 taosExplorer 的地址，默认端口为 `6060`，如果您在本地运行 TDengine, 可以直接访问 http://localhost:6060 用户名和密码默认为：`root/taosdata` 。

## 网络端口

|                        接口或组件名称                        |  容器端口  |  协议   |
| :----------------------------------------------------------: | :--------: | :-----: |
| [原生接口（taosc）](https://docs.taosdata.com/operation/intro/#taosc) |    6030    |   TCP   |
|      [TDgpt](https://docs.taosdata.com/advanced/TDgpt/)      |    6035    |   TCP   |
| [RESTful 接口](https://docs.taosdata.com/reference/connector/rest-api/) |    6041    |   TCP   |
| [WebSocket 接口](https://docs.taosdata.com/develop/connect/#websocket-%E8%BF%9E%E6%8E%A5) |    6041    |   TCP   |
| [taosKeeper](https://docs.taosdata.com/reference/components/taoskeeper/) |    6043    |   TCP   |
| [statsd 格式写入接口](https://docs.taosdata.com/reference/components/taosadapter/#statsd-%E6%95%B0%E6%8D%AE%E5%86%99%E5%85%A5) |    6044    | TCP/UDP |
| [collectd 格式写入接口](https://docs.taosdata.com/reference/components/taosadapter/#collectd-%E6%95%B0%E6%8D%AE%E5%86%99%E5%85%A5) |    6045    | TCP/UDP |
| [openTSDB](https://docs.taosdata.com/reference/components/taosadapter/#opentsdb-json-%E5%92%8C-telnet-%E6%A0%BC%E5%BC%8F%E5%86%99%E5%85%A5) TELNET 格式写入接口 |    6046    |   TCP   |
|          collectd 使用 openTSDB TELNET 格式写入接口          |    6047    |   TCP   |
|          icinga2 使用 openTSDB TELNET 格式写入接口           |    6048    |   TCP   |
|         tcollector 使用 openTSDB TELNET 格式写入接口         |    6049    |   TCP   |
| [taosX](https://docs.taosdata.com/reference/components/taosx/) | 6050, 6055 |   TCP   |
| [taosExplorer](https://docs.taosdata.com/reference/components/explorer/) |    6060    |   TCP   |