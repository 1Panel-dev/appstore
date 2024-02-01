# Elastic

Elastic NV是一家美籍荷兰公司，成立于2012年，位于荷兰阿姆斯特丹，以前称为Elasticsearch。这是一家搜索公司，它构建用于搜索，日志记录，安全性，可观察性和分析用例的自我管理和软件即服务产品。

Elastic NV is an American-Dutch company that was founded in 2012 in Amsterdam, the Netherlands, and was previously known
as Elasticsearch.

## 参考资料

Docker@Elastic: [https://www.docker.elastic.co/](https://www.docker.elastic.co/)

DockerFiles: [https://github.com/elastic/dockerfiles](https://github.com/elastic/dockerfiles)

GitHub Elastic: [https://github.com/elastic](https://github.com/elastic)

官方网站: [https://www.elastic.co/](https://www.elastic.co/)

官方文档: [https://www.elastic.co/guide/index.html](https://www.elastic.co/guide/index.html)

## Elastic Stack

了解可帮助您构建搜索体验、解决问题并取得成功的搜索平台

核心产品包括 Elasticsearch、Kibana、Beats 和 Logstash（也称为 ELK Stack）等等。能够安全可靠地从任何来源获取任何格式的数据，然后对数据进行搜索、分析和可视化。

### ELASTICSEARCH + KIBANA + INTEGRATIONS

集搜索驱动型产品和功能于一身

Elasticsearch 和 Kibana 都是在免费开放的基础上构建而成，适用于各种各样的用例，从日志开始，到您能想到的任何项目，无一不能胜任。Elastic
具备极有价值的功能组合，如 Machine Learning、安全和 Reporting，这些功能专为 Elastic 而生，让我们独树一帜。查看 Elastic Stack
功能的完整列表。

#### Elasticsearch

GitHub: [https://github.com/elastic/elasticsearch](https://github.com/elastic/elasticsearch)

Elasticsearch 是一个基于 JSON 的分布式搜索和分析引擎。
无论您正在查找来自特定 IP 地址的活动，还是正在分析交易请求数量为何突然飙升，或者正在方圆一公里内搜寻美食店，我们尝试解决的这些问题归根结底都是搜索问题。通过
Elasticsearch，您可以快速存储、搜索和分析大量数据。

#### Kibana

GitHub: [https://github.com/elastic/kibana](https://github.com/elastic/kibana)

Kibana 是一个可扩展的用户界面，您可以借助它对数据进行可视化分析。
在 Kibana 中通过炫酷的可视化来探索您的数据，从华夫饼图到热点图，再到时序数据分析，应有尽有。针对多样化数据源使用预配置仪表板，创建实时演示文稿以突出显示
KPI，并使用单一 UI 来管理您的部署。

#### Integrations

通过 Integrations，您可以使用 Elastic Stack 收集并关联数据。
在收集、存储、搜索和分析数据时，发掘有价值的见解。使用 Elastic 代理、Beats
或网络爬虫等功能，从应用程序、基础架构和公共内容源中采集数据，在大量开箱即用型集成功能的加持下，分分钟即可开始工作。

## 版本介绍

### 集群模式

> 8.12.0-cluster

+ Elasticsearch 8.12.0 x3
+ Kibana 8.12.0

> 8.12.0-node

新增节点，需要填写集群信息

+ Elasticsearch 8.12.0

### 单机模式

> 8.12.0-single

+ Elasticsearch 8.12.0
+ Kibana 8.12.0

> 8.12.0-elasticsearch

+ Elasticsearch 8.12.0

> 8.12.0-kibana

+ Kibana 8.12.0

## 安装事项

### 将 vm.max_map_count 设置为至少 262144

vm.max_map_count 内核设置必须至少设置为 262144 才能用于生产。

> Linux
>
> To view the current value for the vm.max_map_count setting, run:
> ```shell
> grep vm.max_map_count /etc/sysctl.conf
> ```
> 显示值大于或等于 262144。即可，如果显示的值小于 262144，请执行以下步骤：

临时设置 vm.max_map_count

```shell
sudo sysctl -w vm.max_map_count=262144
```

永久设置 vm.max_map_count

```shell
sudo vi /etc/sysctl.conf
# 文件末尾添加
vm.max_map_count=262144
# 生效
sudo sysctl -p
```

### 增加 nofile 和 nproc 的 ulimit 值 最小值 65535

> Linux
>
> root 用户 与 普通用户 请注意区别很大
>
> To view the current value for the ulimit setting, run:
> ```shell
> ulimit -n
> ```
> 显示值大于或等于 65535。即可，如果显示的值小于 65535，请执行以下步骤：

临时设置 ulimit

```shell
ulimit -n 65535
```

永久设置 ulimit

**涉及服务器重启**

```shell
sudo vi /etc/security/limits.conf
# 文件末尾添加
root            soft    nofile  unlimited
root            hard    nofile  unlimited
*               soft    nofile  65535
*               hard    nofile  65535
# 生效 重启(重启服务器后生效！！！)
sudo reboot
```

## 日志配置

当前采用 `JSON File logging driver` 记录日志
