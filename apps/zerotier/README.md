# Zerotier

ZeroTier 这一类 P2P VPN 是在互联网的基础上将自己的所有设备组成一个私有的网络，可以理解为互联网连接的局域网。最常见的场景就是在公司可以用手机直接访问家里的
NAS，而且是点对点直连，数据传输并不经由第三方服务器中转。

ZeroTier 在多设备之间建立了一个 Peer to Peer VPN（P2PVPN） 连接，如：在笔记本电脑、台式机、嵌入式设备、云资源和应用。这些设备只需要通过
ZeroTier One ( ZeroTier 的客户端) 在不同设备之间建立直接连接，即使它们位于 NAT 之后。连接到虚拟 LAN 的任何计算机和设备通常通过
NAT 或路由器设备与 Internet 连接，ZeroTier One 使用 STUN 和隧道来建立 NAT 后设备之间的 VPN 直连。

简单一点说，ZeroTier 就是通过 P2P 等方式实现形如交换机或路由器上 LAN 设备的内网互联。

### 专有名词

PLANET ：行星服务器，Zerotier 根服务器

MOON ：卫星服务器，用户自建的私有根服务器，起到代理加速的作用

LEAF ：网络客户端，就是每台连接到网络节点。

### 安装说明

目录映射  `/var/lib/zerotier-one` 为容器内的数据目录，可以映射到宿主机的目录，以便数据持久化。
与 ZeroTier 官方安装目录一致，可以直接使用官方的配置文件。

#### 替换 Planet

如果你想使用自己的 PLANET 服务器, 在应用停止状态下，替换官方文件。
`/var/lib/zerotier-one/planet`

### 常用命令

进入基础目录：`cd /var/lib/zerotier-one` 或使用完整路径 `/var/lib/zerotier-one/zerotier-cli`

获取您的 ZeroTier 地址并检查服务状态

```shell
zerotier-cli status
```

加入网络

```shell
zerotier-cli join ${NETWORK_ID}

```

离开网络

```shell
zerotier-cli leave ${NETWORK_ID}

```

列出网络

```shell
zerotier-cli listnetworks
```
