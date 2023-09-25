# 使用说明

- 默认账户密码
```
username：admin
password：hertzbeat
``````
# 原始相关

<p align="center">
  <a href="https://hertzbeat.com">
     <img alt="hertzbeat" src="https://cdn.jsdelivr.net/gh/dromara/hertzbeat/home/static/img/hertzbeat-brand.svg" width="260">
  </a>
</p>


## HertzBeat 赫兹跳动 

> 易用友好的开源实时监控告警系统，无需Agent，高性能集群，强大自定义监控能力。

**官网: [hertzbeat.dromara.org](https://hertzbeat.dromara.org) | [hertzbeat.com](https://hertzbeat.com)**

**云服务: [tancloud.cn](https://tancloud.cn)**

## 🎡 <font color="green">介绍</font>

[HertzBeat 赫兹跳动](https://github.com/dromara/hertzbeat) 是一个拥有强大自定义监控能力，高性能集群，无需 Agent 的开源实时监控告警系统。

### 特点

- 集 **监控+告警+通知** 为一体，支持对应用服务，数据库，操作系统，中间件，云原生，网络等监控阈值告警通知一步到位。
- 易用友好，无需 `Agent`，全 `WEB` 页面操作，鼠标点一点就能监控告警，零上手学习成本。
- 将 `Http,Jmx,Ssh,Snmp,Jdbc` 等协议规范可配置化，只需在浏览器配置监控模版 `YML` 就能使用这些协议去自定义采集想要的指标。您相信只需配置下就能立刻适配一款 `K8s` 或 `Docker` 等新的监控类型吗？
- 高性能，支持多采集器集群横向扩展，支持多隔离网络监控，云边协同。
- 自由的告警阈值规则，`邮件` `Discord` `Slack` `Telegram` `钉钉` `微信` `飞书` `短信` `Webhook` `Server酱` 等方式消息及时送达。


> `HertzBeat`的强大自定义，多类型支持，高性能，易扩展，低耦合，希望能帮助用户快速搭建自有监控系统。    
> 当然我们也提供了对应的 **[SAAS版本监控云服务](https://console.tancloud.cn)**，中小团队和个人无需再为监控自有资源而去部署一套监控系统，**[登录即可免费开始](https://console.tancloud.cn)**。

----   


## ⛄ 已支持

> 我们将监控采集类型(mysql,jvm,k8s)都定义为yml监控模版，用户可以导入这些模版来支持对应类型的监控!    
> 欢迎大家一起贡献你使用过程中自定义的通用监控类型监控模版。

- [Website](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-website.yml), [Port Telnet](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-port.yml),
  [Http Api](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-api.yml), [Ping Connect](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-ping.yml),
  [Jvm](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-jvm.yml), [SiteMap](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-fullsite.yml),
  [Ssl Certificate](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-ssl_cert.yml), [SpringBoot2](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-springboot2.yml),
  [FTP Server](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-ftp.yml), [SpringBoot3](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-springboot3.yml)
- [Mysql](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-mysql.yml), [PostgreSQL](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-postgresql.yml),
  [MariaDB](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-mariadb.yml), [Redis](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-redis.yml),
  [ElasticSearch](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-elasticsearch.yml), [SqlServer](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-sqlserver.yml),
  [Oracle](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-oracle.yml), [MongoDB](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-mongodb.yml),
  [DM](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-dm.yml), [OpenGauss](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-opengauss.yml),
  [ClickHouse](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-clickhouse.yml), [IoTDB](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-iotdb.yml),
  [Redis Cluster](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-redis_cluster.yml), [Redis Sentinel](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-redis_sentinel.yml)
  [Doris BE](https://github.com/dromara/hertzbeat/blob/master/manager/src/main/resources/define/app-doris_be.yml),
  [Doris FE](https://github.com/dromara/hertzbeat/blob/master/manager/src/main/resources/define/app-doris_fe.yml)
- [Linux](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-linux.yml), [Ubuntu](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-ubuntu.yml),
  [CentOS](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-centos.yml), [Windows](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-windows.yml),
  [EulerOS](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-euleros.yml), [Fedora CoreOS](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-coreos.yml),
  [OpenSUSE](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-opensuse.yml), [Rocky Linux](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-rockylinux.yml),
  [Red Hat](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-redhat.yml), [FreeBSD](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-freebsd.yml),
  [AlmaLinux](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-almalinux.yml), [Debian Linux](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-debian.yml)
- [Tomcat](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-tomcat.yml), [Nacos](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-nacos.yml),
  [Zookeeper](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-zookeeper.yml), [RabbitMQ](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-rabbitmq.yml),
  [Flink](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-flink.yml), [Kafka](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-kafka.yml),
  [ShenYu](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-shenyu.yml), [DynamicTp](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-dynamic_tp.yml),
  [Jetty](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-jetty.yml), [ActiveMQ](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-activemq.yml)
- [Kubernetes](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-kubernetes.yml), [Docker](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-docker.yml)
- [CiscoSwitch](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-cisco_switch.yml), [HpeSwitch](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-hpe_switch.yml),
  [HuaweiSwitch](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-huawei_switch.yml), [TpLinkSwitch](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-tplink_switch.yml),
  [H3cSwitch](https://raw.githubusercontent.com/dromara/hertzbeat/master/manager/src/main/resources/define/app-h3c_switch.yml)
- 和更多自定义监控模版。
- 通知支持 `Discord` `Slack` `Telegram` `邮件` `钉钉` `微信` `飞书` `短信` `Webhook` `Server酱`。

## 🐕 快速开始

- 如果您不想部署而是直接使用，我们提供SAAS监控云服务-TanCloud探云，**[即刻登录注册免费使用](https://console.tancloud.cn)**。
- 如果您是想将HertzBeat部署到内网环境搭建监控系统，请参考下面的部署文档进行操作。

### 🍞 HertzBeat安装
> HertzBeat支持通过源码安装启动，Docker容器运行和安装包方式安装部署，CPU架构支持x86/arm64。

##### 方式一：Docker方式快速安装

1. `docker` 环境仅需一条命令即可开始

```docker run -d -p 1157:1157 -p 1158:1158 --name hertzbeat tancloud/hertzbeat```

```或者使用 quay.io (若 dockerhub 网络链接超时)```

```docker run -d -p 1157:1157 -p 1158:1158 --name hertzbeat quay.io/tancloud/hertzbeat```

2. 浏览器访问 `http://localhost:1157` 即可开始，默认账号密码 `admin/hertzbeat`

3. 部署采集器集群

```
docker run -d -e IDENTITY=custom-collector-name -e MANAGER_HOST=127.0.0.1 -e MANAGER_PORT=1158 --name hertzbeat-collector tancloud/hertzbeat-collector
```
- `-e IDENTITY=custom-collector-name` : 配置此采集器的唯一性标识符名称，多个采集器名称不能相同，建议自定义英文名称。
- `-e MODE=public` : 配置运行模式(public or private), 公共集群模式或私有云边模式。
- `-e MANAGER_HOST=127.0.0.1` : 配置连接主HertaBeat服务的对外IP。
- `-e MANAGER_PORT=1158` : 配置连接主HertzBeat服务的对外端口，默认1158。

更多配置详细步骤参考 [通过Docker方式安装HertzBeat](https://hertzbeat.com/docs/start/docker-deploy)

##### 方式二：通过安装包安装

1. 下载您系统环境对应的安装包`hertzbeat-xx.zip` [GITEE Release](https://gitee.com/dromara/hertzbeat/releases) [GITHUB Release](https://github.com/dromara/hertzbeat/releases)
2. 需要提前已安装`java jdk11`环境
3. 配置 HertzBeat 的配置文件 `hertzbeat/config/application.yml`(可选)
4. 部署启动 `$ ./bin/startup.sh ` 或 `bin/startup.bat`
5. 浏览器访问 `http://localhost:1157` 即可开始，默认账号密码 `admin/hertzbeat`
6. 部署采集器集群
  - 下载采集器安装包`hertzbeat-collector-xx.zip`到规划的另一台部署主机上 [GITEE Release](https://gitee.com/dromara/hertzbeat/releases) [GITHUB Release](https://github.com/dromara/hertzbeat/releases)
  - 需要提前已安装`java jdk11`环境
  - 配置采集器的配置文件 `hertzbeat-collector/config/application.yml` 里面的连接主HertzBeat服务的对外IP，端口，当前采集器名称(需保证唯一性)等参数 `identity` `mode` (public or private) `manager-host` `manager-port`
    ```yaml
    collector:
      dispatch:
        entrance:
          netty:
            enabled: true
            identity: ${IDENTITY:}
            mode: ${MODE:public}
            manager-host: ${MANAGER_HOST:127.0.0.1}
            manager-port: ${MANAGER_PORT:1158}
    ```
  - 启动 `$ ./bin/startup.sh ` 或 `bin/startup.bat`
  - 浏览器访问主HertzBeat服务 `http://localhost:1157` 查看概览页面即可看到注册上来的新采集器

更多配置详细步骤参考 [通过安装包安装HertzBeat](https://hertzbeat.com/docs/start/package-deploy)

##### 方式三：本地代码启动

1. 此为前后端分离项目，本地代码调试需要分别启动后端工程`manager`和前端工程`web-app`
2. 后端：需要`maven3+`, `java11`和`lombok`环境，修改`YML`配置信息并启动`manager`服务
3. 前端：需要`nodejs npm angular-cli`环境，待本地后端启动后，在`web-app`目录下启动 `ng serve --open`
4. 浏览器访问 `http://localhost:4200` 即可开始，默认账号密码 `admin/hertzbeat`

详细步骤参考 [参与贡献之本地代码启动](CONTRIBUTING.md)

##### 方式四：Docker-Compose 统一安装 hertzbeat+mysql+iotdb/tdengine

通过 [Docker-Compose 部署脚本](script/docker-compose) 一次性把 mysql 数据库, iotdb/tdengine 时序数据库和 hertzbeat 安装部署。

详细步骤参考 [通过Docker-Compose安装HertzBeat](script/docker-compose/README.md)

##### 方式五：Kubernetes Helm Charts 部署 hertzbeat+collector+mysql+iotdb

通过 Helm Chart 一次性将 HertzBeat 集群组件部署到 Kubernetes 集群中。

详细步骤参考 [Artifact Hub](https://artifacthub.io/packages/helm/hertzbeat/hertzbeat)

**HAVE FUN**

## 💬 社区交流

HertzBeat 赫兹跳动是 [Dromara开源社区](https://dromara.org/) 下顶级项目。Gitee GVP。

##### 微信交流群

加微信号 tan-cloud 或 扫描下面账号二维码拉您进微信群。   
<img alt="tan-cloud" src="home/static/img/docs/help/tan-cloud-wechat.jpg" width="200"/>

##### QQ交流群

加QQ群号 236915833 或 扫描下面的群二维码进群

<img alt="tan-cloud" src="home/static/img/docs/help/qq-qr.jpg" width="200"/>          

##### Channel

[Gitter Channel](https://gitter.im/hertzbeat/community)

[Github Discussion](https://github.com/dromara/hertzbeat/discussions)

[User Club](https://support.qq.com/products/379369)

## 🛡️ License
[`Apache License, Version 2.0`](https://www.apache.org/licenses/LICENSE-2.0.html)
