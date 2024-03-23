# ztncui

包含 ZeroTier One 和 ztncui 的 Docker 映像，用于在容器中设置具有 Web 用户界面的独立 ZeroTier 网络控制器。

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
