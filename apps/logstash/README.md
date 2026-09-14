## 产品介绍

**Logstash** 是 Elastic Stack 的一部分，可与 Beats、Elasticsearch 和 Kibana 协同工作。根据 Elastic 官方 GitHub README，Logstash 是一个服务器端数据处理管道，能够同时从多种来源采集数据、进行转换，并将数据发送到目标存储。

## 安装说明

如果需要把接收到的数据发送到 Elasticsearch，请编辑应用目录中的 `pipeline/logstash.conf`，按文件中的官方示例启用 `elasticsearch` 输出配置，并将 `hosts` 改为实际可达的 Elasticsearch 地址。

## 主要功能

- 从多种数据源采集事件
- 在传输过程中解析、过滤和转换数据
- 通过插件将数据发送到不同目标
- 依靠丰富插件扩展输入、过滤和输出能力
