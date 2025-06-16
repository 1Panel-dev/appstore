# 使用说明

## 服务地址

- 访问地址：`http://IP:8848/nacos`
- 用户名：`nacos`
- 密码：`nacos`


### 如果是nacos3.0+版本

- 访问地址：`http://IP:8080/index.html`
- 用户名：`nacos`
- 密码：首次打开会要求初始化管理员用户nacos的密码

## 参数调优

```shell
- JVM_XMS=64m   # -Xms default :2g
- JVM_XMX=64m   # -Xmx default :2g
- JVM_XMN=16m   # -Xmn default :1g
- JVM_MS=8m     # -XX:MetaspaceSize default :128m
- JVM_MMS=8m    # -XX:MaxMetaspaceSize default :320m
```

# Nacos

**Nacos** 是一个更易于构建云原生应用的动态服务发现、配置管理和服务管理平台。

## Nacos 的关键特性

## 特色：

- **易用**：动态服务发现的一站式解决方案。配置管理和动态 DNS服务。提供 20 多项开箱即用的特性，适用于面向服务的架构。轻量级的生产就绪控制台。
- **可靠**：无缝支持 Kubernetes 和 Spring Cloud，更容易在流行的公共云（例如阿里云和 AWS）上部署和运行，支持多租户和多环境。
- **云原生**：源自阿里巴巴集团经过时间验证的内部产品。支持具有数百万服务规模的大型场景。开源产品并提供企业级的服务级别协议（SLA）。
- **可扩展**：支持速率限制、大规模推广计划和多区域主动-主动架构。直接或稍作扩展支持各种相关的基于互联网的使用案例。流量调度和服务治理。