# EasyTier
> ✨ 一个由 Rust 和 Tokio 驱动的简单、安全、去中心化的异地组网方案
📚 **[完整文档](https://easytier.cn)** | 🖥️ **[Web 控制台](https://easytier.cn/web)** | 📝 **[下载发布版本](https://github.com/EasyTier/EasyTier/releases)** | 🧩 **[第三方工具](https://easytier.cn/guide/installation_gui.html#%E7%AC%AC%E4%B8%89%E6%96%B9%E5%9B%BE%E5%BD%A2%E7%95%8C%E9%9D%A2)** | ❤️ **[赞助](#赞助)**

## 🚀 快速开始

> **💡 提示**：EasyTier 提供两种部署方式，官方托管服务适合快速体验，自建服务适合企业级应用

### 📥 安装方式

<details open>
<summary><strong>🌐 方式一：官方 Web 控制台（推荐新手）</strong></summary>

#### 步骤说明

1. **注册账号**  
   访问 [EasyTier Web 控制台](https://easytier.cn/web)，点击注册并完成用户账号创建

2. **获取用户名**  
   登录后，在个人信息页面可以看到你的 EasyTier 用户名（如：`easyeen`）

3. **下载客户端**  
   下载适用于你平台的 EasyTier 客户端程序，并根据客户端指引填写你的用户名（如：`easyeen`）

4. **启动连接**  
   启动 EasyTier 客户端后，即可自动接入官方托管的 EasyTier 网络

5. **管理网络**  
   节点的添加、网络的创建及成员管理，均推荐通过 Web 控制台操作，界面友好且操作简单

</details>

<details>
<summary><strong>🏗️ 方式二：自建服务部署（推荐企业）</strong></summary>

#### 部署步骤

1. **准备环境**  
   准备一台云服务器或本地主机，并下载相应版本的 EasyTier 服务端

2. **部署节点**  
   根据[官方文档](https://easytier.cn/guide/introduction.html)或 [Docker 方式](https://hub.docker.com/r/easytier/easytier)按照说明部署 EasyTier 节点

3. **配置端口**  
   部署完成后，服务会监听 UDP 端口（如默认 `22020`），请确保服务器安全组已开放对应端口

4. **配置用户**  
   用户端配置时，需要在用户名前添加自己的节点地址  
   **格式**：`udp://你的服务器地址:端口/用户名`  
   **示例**：`udp://1.2.3.4:22020/easyeen`

5. **连接网络**  
   通过客户端填入上述完整标识信息，即可接入你自建的 EasyTier 网络

6. **管理维护**  
   节点的管理、网络组的创建也可按需求通过本地或网页管理界面操作

</details>

---

> **✨ 总结**：无论选择官方托管服务还是自建节点模式，所有节点和网络均可通过统一的方式进行管理和访问，灵活支持异地组网和分布式管理需求。

## 特性

### 核心特性

- 🔒 **去中心化**：节点平等且独立，无需中心化服务
- 🚀 **易于使用**：支持通过网页、客户端和命令行多种操作方式
- 🌍 **跨平台**：支持 Win/MacOS/Linux/FreeBSD/Android 和 X86/ARM/MIPS 架构
- 🔐 **安全**：AES-GCM 或 WireGuard 加密，防止中间人攻击

### 高级功能

- 🔌 **高效 NAT 穿透**：支持 UDP 和 IPv6 穿透，可在 NAT4-NAT4 网络中工作
- 🌐 **子网代理**：节点可以共享子网供其他节点访问
- 🔄 **智能路由**：延迟优先和自动路由选择，提供最佳网络体验
- ⚡ **高性能**：整个链路零拷贝，支持 TCP/UDP/WSS/WG 协议

### 网络优化

- 📊 **UDP 丢包抗性**：KCP/QUIC 代理在高丢包环境下优化延迟和带宽
- 🔧 **Web 管理**：通过 Web 界面轻松配置和监控
- 🛠️ **零配置**：静态链接的可执行文件，简单部署

## 相关项目

- [ZeroTier](https://www.zerotier.com/)：用于连接设备的全球虚拟网络。
- [TailScale](https://tailscale.com/)：旨在简化网络配置的 VPN 解决方案。
- [vpncloud](https://github.com/dswd/vpncloud)：一个 P2P 网状 VPN
- [Candy](https://github.com/lanthora/candy)：一个可靠、低延迟、反审查的虚拟专用网络

### 联系我们

- 💬 **[Telegram 群组](https://t.me/easytier)**
- 👥 **QQ 群**
  - 一群 [949700262](https://qm.qq.com/q/wFoTUChqZW)
  - 二群 [837676408](https://qm.qq.com/q/4V33DrfgHe)
  - 三群 [957189589](https://qm.qq.com/q/YNyTQjwlai)

## 许可证

EasyTier 在 [LGPL-3.0](https://github.com/EasyTier/EasyTier/blob/main/LICENSE) 许可下发布。
