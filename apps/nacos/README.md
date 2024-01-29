# Nacos

Nacos `/nɑ:kəʊs/` 是 Dynamic Naming and Configuration Service的首字母简称，一个更易于构建云原生应用的动态服务发现、配置管理和服务管理平台。

Nacos 致力于帮助您发现、配置和管理微服务。Nacos 提供了一组简单易用的特性集，帮助您快速实现动态服务发现、服务配置、服务元数据及流量管理。

Nacos 帮助您更敏捷和容易地构建、交付和管理微服务平台。 Nacos 是构建以“服务”为中心的现代应用架构 (
例如微服务范式、云原生范式) 的服务基础设施。

## 快速启动

访问地址
`http://IP:8848/nacos`

> 用户名
> nacos
>
> 密码
> nacos

## Nacos 的关键特性

### 服务发现和服务健康监测

Nacos 支持基于 DNS 和基于 RPC 的服务发现。服务提供者使用 原生SDK、OpenAPI、或一个独立的Agent TODO注册 Service
后，服务消费者可以使用DNS TODO 或HTTP&API查找和发现服务。

Nacos 提供对服务的实时的健康检查，阻止向不健康的主机或服务实例发送请求。Nacos 支持传输层 (PING 或 TCP)和应用层 (如
HTTP、MySQL、用户自定义）的健康检查。 对于复杂的云环境和网络拓扑环境中（如 VPC、边缘网络等）服务的健康检查，Nacos 提供了 agent
上报模式和服务端主动检测2种健康检查模式。Nacos 还提供了统一的健康检查仪表盘，帮助您根据健康状态管理服务的可用性及流量。

### 动态配置服务

动态配置服务可以让您以中心化、外部化和动态化的方式管理所有环境的应用配置和服务配置。

动态配置消除了配置变更时重新部署应用和服务的需要，让配置管理变得更加高效和敏捷。

配置中心化管理让实现无状态服务变得更简单，让服务按需弹性扩展变得更容易。

Nacos 提供了一个简洁易用的UI (控制台样例 Demo) 帮助您管理所有的服务和应用的配置。Nacos
还提供包括配置版本跟踪、金丝雀发布、一键回滚配置以及客户端配置更新状态跟踪在内的一系列开箱即用的配置管理特性，帮助您更安全地在生产环境中管理配置变更和降低配置变更带来的风险。

### 动态 DNS 服务

动态 DNS 服务支持权重路由，让您更容易地实现中间层负载均衡、更灵活的路由策略、流量控制以及数据中心内网的简单DNS解析服务。动态DNS服务还能让您更容易地实现以
DNS 协议为基础的服务发现，以帮助您消除耦合到厂商私有服务发现 API 上的风险。

Nacos 提供了一些简单的 DNS APIs TODO 帮助您管理服务的关联域名和可用的 IP 列表.

### 服务及其元数据管理

Nacos 能让您从微服务平台建设的视角管理数据中心的所有服务及元数据，包括管理服务的描述、生命周期、服务的静态依赖分析、服务的健康状态、服务的流量管理、路由及安全策略、服务的
SLA 以及最首要的 metrics 统计数据。

## 版本说明

### 2.2.0-derby

derby 单机模式

数据存于本地文件目录
`/home/nacos`

### 2.2.0-mysql

derby 单机模式 + mysql
数据存于mysql

#### 数据库初始化

下载数据库文件 `mysql-schema.sql`

[https://github.com/alibaba/nacos/tree/develop/distribution/conf](https://github.com/alibaba/nacos/tree/develop/distribution/conf)

导入数据文件 `mysql-schema.sql` 或通过工具导入/执行语句

```shell
mysql -uroot -p123456 -h127.0.0.1 -P3306 nacos < mysql-schema.sql
```

## 参数调优

```shell
- JVM_XMS=64m   #-Xms default :2g
- JVM_XMX=64m   #-Xmx default :2g
- JVM_XMN=16m   #-Xmn default :1g
- JVM_MS=8m     #-XX:MetaspaceSize default :128m
- JVM_MMS=8m    #-XX:MaxMetaspaceSize default :320m
```
