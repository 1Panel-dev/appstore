# 介绍

Woodpecker 是一款 CI/CD 工具。它设计轻量、操作简便且运行迅捷。

> 安装时需要配置托管平台：
> 以 GitHub 为例，点开设置，进入开发者设置，再进入 OAuth 应用中，注册一个新应用：
> 其他随意填写，回调网址必须填写 `https://<Woodpecker实例地址>/authorize`。

## 特点

- Woodpecker 使用 Docker 容器执行管道步骤。若您需要超越常规 Docker 镜像的功能，可创建插件来扩展管道功能。
- Woodpecker 始终是完全免费的。由于 Woodpecker 的源代码是开源的，您可以贡献力量来帮助项目发展。
- Woodpecker 让您能够轻松为项目创建多个工作流。这些工作流甚至可以相互依赖。

## 构成

Woodpecker 由核心组件（`server` 和 `agent`）及可选组件（`autoscaler`）构成。

`server` 提供用户界面，处理发往底层 `forge` （托管平台）的 Webhook 请求，提供 API 服务，并解析 YAML 文件中的管道配置。

`agent` 通过特定后端（Docker、 Kubernetes、 本地）执行工作流，并通过 GRPC 连接至服务器。多个代理可同时存在，从而针对单个实例精细调整作业限制、后端选择及其他代理相关设置。

`autoscaler` 可在选定的云服务商上启动新虚拟机处理待处理构建任务。构建完成后，虚拟机将在短暂过渡期后自动销毁。
