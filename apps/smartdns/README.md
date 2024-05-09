# SmartDNS

SmartDNS 是一个运行在本地的 DNS 服务器，它接受来自本地客户端的 DNS 查询请求，然后从多个上游 DNS 服务器获取 DNS 查询结果，并将访问速度最快的结果返回给客户端，以此提高网络访问速度。
SmartDNS 同时支持指定特定域名 IP 地址，并高性匹配，可达到过滤广告的效果; 支持DOT(DNS over TLS)和DOH(DNS over HTTPS)，更好的保护隐私。  

与 DNSmasq 的 all-servers 不同，SmartDNS 返回的是访问速度最快的解析结果。

支持树莓派、OpenWrt、华硕路由器原生固件和 Windows 系统等。

## 软件效果展示

**阿里 DNS**
使用阿里 DNS 查询百度IP，并检测结果。

```shell
$ nslookup www.baidu.com 223.5.5.5
Server:         223.5.5.5
Address:        223.5.5.5#53

Non-authoritative answer:
www.baidu.com   canonical name = www.a.shifen.com.
Name:   www.a.shifen.com
Address: 180.97.33.108
Name:   www.a.shifen.com
Address: 180.97.33.107

$ ping 180.97.33.107 -c 2
PING 180.97.33.107 (180.97.33.107) 56(84) bytes of data.
64 bytes from 180.97.33.107: icmp_seq=1 ttl=55 time=24.3 ms
64 bytes from 180.97.33.107: icmp_seq=2 ttl=55 time=24.2 ms

--- 180.97.33.107 ping statistics ---
2 packets transmitted, 2 received, 0% packet loss, time 1001ms
rtt min/avg/max/mdev = 24.275/24.327/24.380/0.164 ms
pi@raspberrypi:~/code/smartdns_build $ ping 180.97.33.108 -c 2
PING 180.97.33.108 (180.97.33.108) 56(84) bytes of data.
64 bytes from 180.97.33.108: icmp_seq=1 ttl=55 time=31.1 ms
64 bytes from 180.97.33.108: icmp_seq=2 ttl=55 time=31.0 ms

--- 180.97.33.108 ping statistics ---
2 packets transmitted, 2 received, 0% packet loss, time 1001ms
rtt min/avg/max/mdev = 31.014/31.094/31.175/0.193 ms
```

**SmartDNS**
使用 SmartDNS 查询百度 IP，并检测结果。

```shell
$ nslookup www.baidu.com
Server:         192.168.1.1
Address:        192.168.1.1#53

Non-authoritative answer:
www.baidu.com   canonical name = www.a.shifen.com.
Name:   www.a.shifen.com
Address: 14.215.177.39

$ ping 14.215.177.39 -c 2
PING 14.215.177.39 (14.215.177.39) 56(84) bytes of data.
64 bytes from 14.215.177.39: icmp_seq=1 ttl=56 time=6.31 ms
64 bytes from 14.215.177.39: icmp_seq=2 ttl=56 time=5.95 ms

--- 14.215.177.39 ping statistics ---
2 packets transmitted, 2 received, 0% packet loss, time 1001ms
rtt min/avg/max/mdev = 5.954/6.133/6.313/0.195 ms
```

从对比看出，SmartDNS 找到了访问 www.baidu.com 最快的 IP 地址，比阿里 DNS 速度快了 5 倍。

## 特性

* **多上游服务器：** 支持配置多个上游 DNS 服务器，并同时进行查询。
* **返回最快地址：** 支持从域名所属 IP 地址列表中查找到访问速度最快的 IP 地址。
* **多种查询协议：** 支持 UDP、TCP、DOT 和 DOH 查询及服务，以及非 53 端口查询。
* **特定域名 IP 地址指定：** 支持指定域名的 IP 地址，达到广告过滤效果、避免恶意网站的效果。
* **域名高性能后缀匹配：** 支持域名后缀匹配模式，简化过滤配置，过滤 20 万条记录时间 < 1ms。
* **域名分流：** 支持域名分流，不同类型的域名向不同的 DNS 服务器查询。
* **多平台支持：** 支持标准 Linux 系统、OpenWrt 系统各种固件和华硕路由器原生固件。
* **支持双栈协议：** 支持 IPv4 和 IPV 6网络，支持查询 A 和 AAAA 记录，并支持完全禁用 IPv6 AAAA 解析。
* **支持 DNS64：** 支持DNS64转换。
* **高性能、占用资源少：** 多线程异步 IO 模式，cache 缓存查询结果。
* **主流系统官方支持：** 主流路由系统官方软件源安装smartdns。

## 开源声明

SmartDNS 基于 GPL V3 协议开源。
