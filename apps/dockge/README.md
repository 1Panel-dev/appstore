# Dockge

DockgeDockge是一款简化、易于使用的自托管容器编排工具。

<img src="https://github.com/louislam/dockge/assets/1336778/26a583e1-ecb1-4a8d-aedf-76157d714ad7" width="900" alt="" />

## 主要特性：

- 通过Web页面管理```compose.yaml```文件。

  - 创建/编辑/启动/停止/重新启动/删除容器。
  - 更新Docker镜像。

- 交互式Web终端。

- 响应式设计，实时更新进度（Pull/Up/Down）和Web终端输出。

- 提供了简化的用户体验，可以在一个页面上方便地找到您需要的一切，一目了然。

  - 由Uptime Kuma的创作者开发。

- 支持将```docker run … ```命令转换为```compose.yaml```配置。

- 基于文件结构，支持使用正常的 ```docker compose ```命令进行交互。

  <img src="https://github.com/louislam/dockge/assets/1336778/cc071864-592e-4909-b73a-343a57494002" width=300 />

## FAQ ##

- 我能否管理已存在堆栈？
  * 可以，将compose文件移动至stacks路径，并扫描堆栈文件夹。1Panel下默认路径```/opt/1panel/apps/dockge/stacks```。
    * 注意此处必须为绝对路径，使用相对路径将导致compose文件保存路径错误。
- Dockge是Portainer替代品吗？
  - 不完全是。Portainer提供许多Docker功能，而Dockge目前仅专注于docker compose，提供更好的用户界面和体验。如果只是使用docker compose管理容器，答案是肯定的。如果还需要管理诸如docker网络，答案也许是否定的。
- 可以同时安装Dockge和Portainer吗？
  - 可以。

