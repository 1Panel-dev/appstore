## 产品介绍

Forgejo Runner 是一个守护进程，用于连接到 Forgejo 实例并运行持续集成 (CI) 作业。该项目是 Forgejo 生态系统的一部分，旨在为 Forgejo 用户提供类似 GitHub Actions 的自动化工作流能力。其核心价值在于为自托管的 Forgejo 实例提供原生的 CI/CD 功能，目标用户是需要自动化构建、测试和部署流程的开发团队和组织，特别是在需要自托管解决方案的场景中。

## 主要功能

- **持续集成作业执行**: 作为守护进程连接到 Forgejo 实例，自动拉取并执行工作流中定义的 CI/CD 任务，支持开发者实现自动化构建、测试和部署流程。

- **多架构支持**: 目前正式支持在基于 Linux 内核的操作系统上运行，涵盖 `amd64` 和 `arm64` 架构，并提供二进制文件和容器镜像，同时正在积极开发对其他架构 (如 s390x、powerpc64le、riscv64 和 Windows) 的支持。

- **与 Forgejo 深度集成**: 作为 Forgejo 的原生 Actions 运行器，能够无缝集成到 Forgejo 的工作流系统中，用户可以通过 Forgejo 界面配置和管理自动化任务。

## Docker 守护进程运行模式风险提示

### Docker outside of Docker (DooD)

此模式下 Runner 服务容器 (即本应用创建的容器) 及作业容器 (即实际运行工作流的容器) 与宿主机共享 Docker 环境。这可能导致以下风险:
  - 任何在宿主机 Docker 中运行的容器都将被作业容器、服务容器和步骤中的容器访问。例如，如果 Forgejo 或 Forgejo Runner 在相同的主机上作为容器运行，那么作业容器将能够执行对这些容器上的 docker kill 、 docker inspect 或 docker exec 等操作。

  > ```yaml
  > jobs:
  >   docker-access-demo:
  >     runs-on: docker
  >       container:
  >         image: node:lts
  >       services:
  >         postgres:
  >           image: postgres:latest
  >           env:
  >             POSTGRES_PASSWORD: example
  >       steps:
  >        - run: docker run -d --name ubuntu ubuntu:latest
  > ```
  >   上方的示例中，node、postgres、ubuntu 三个容器均可访问到宿主机中的其他容器

  - 将卷挂载到新创建的容器，可能会危及宿主机上的所有存储。诸如`docker run -v /:/host-mount ubuntu`这样的命令出现在工作流中会使整个主机的存储 (包括磁盘上的任何密钥) 暴露给工作流。
  - 工作流创建的工件，例如通过 docker build 构建的镜像，或通过 docker run 创建的容器，都将留在宿主机的 docker 守护进程中。后续在同一运行器上执行的任何工作流都能访问它们，并可能从中提取机密信息。
  - 在 Forgejo Runner 服务容器上配置的资源限制对由工作流创建的作业容器没有任何影响。
  - 通过 Forgejo Runner 的 container.network 指定的网络配置不会应用于工作流创建的任何容器。工作流可以定义诸如 --network=host 等来访问主机操作系统的原生网络。将本地网络资源暴露给这些容器，会带来新的安全风险。

### Docker in Docker (DinD)

此模式下的作业容器 (即实际运行工作流的容器) 与宿主机的 Docker 环境隔离，具有一定的安全性。但是仍需注意:
  - 如果 Forgejo Runner 配置了大于 1 的 runner.capacity 以允许多个并发任务，或者多个 Forgejo Runner 使用相同的 container.docker_host 连接上了同一个 DinD 实例，则作业容器之间可通过 docker 客户端相互查看并交互。这可能允许一个任务容器在运行时窃取另一个正在运行的作业容器的密钥或与其文件交互。
  - DinD 容器将拥有自己的存储，在其容器内的自己的 /var/lib/docker 目录中。这会导致:
    - 主机上的镜像在容器内拉取之前，不会在 DinD 环境内可用；
    - 宿主机上已有的镜像将会在 DinD 环境内额外保存一次；
    - 除非将/var/lib/docker 目录持久化，否则在重建 DinD 容器 (例如升级 docker:dind 镜像时) 会清空该目录，若工作流依赖镜像缓存，则会影响之后工作流的效率。
    - 如果/var/lib/docker 被映射到持久卷，则必须定期执行诸如 docker system prune 之类的维护操作，以确保镜像和卷不会因过时和无用的内容而无限增长。
  - 工作流创建的工件，例如通过 docker build 构建的镜像，或通过 docker run 创建的容器，会留在 DinD 守护进程中。后续在同一运行器上执行的任何工作流都能访问它们，并可能从中提取机密信息。
  - 在 Forgejo Runner 服务容器上配置的资源限制对由工作流创建的作业容器没有任何影响，这些资源实际上将受限于所创建的 DinD 容器的配置。

## 配置和使用说明

- **Forgejo 实例 URL**: (例如 `https://git.example.com`) 
- **注册令牌**: 从 Create new runner 按钮中获取，仅用于 Runner 实例，需要区别于个人访问令牌 (PAT) 
- **运行器名称**: 建议为每个 Runner 设置具有描述性的唯一名称 (如 ForgejoRunner-机房-用途-编号)
- **运行器标签**: 用于标记 Runner 实例，工作流中可以根据标签选择 Runner 执行任务，同一个标签可以绑定多个 Runner 实例
- 工作流默认将会在基于 `node:lts` 的容器中运行，如何需要使用其他环境，可以在工作流中配置
  ```yaml
  jobs:
    test:
      runs-on: docker
      container:
        image: pytorch/pytorch:2.10.0-cuda13.0-cudnn9-runtime
        options: 
          -v /path/to/folder:/path/to/folder
      steps:
        - run: ls -al /path/to/folder
  ```
- 工作流默认将会在基于 `node:lts` 的容器中运行，如果需要用到 docker 客户端，需要自行安装
  ```yaml
  on:
    workflow_dispatch:
  jobs:
    test:
      runs-on: docker
      steps:
        - run: |
            curl -fsSL https://get.docker.com | bash
            docker -v
            docker ps
  ```