# 使用说明

- 默认用户名`admin`

# 原始相关

# 一分钟自建zerotier-planet

私有部署zeroteir-planet服务
基于 [ztncui](https://github.com/key-networks/ztncui-aio) 整理成 docker-compose.yml 文件.

**特别感谢** <https://github.com/Jonnyan404/zerotier-planet/issues/11#issuecomment-1059961262> 这个issue中各位用户的贡献，基于此issue中 `@jqtmviyu` 的步骤和`kaaass`的 [mkmoonworld](https://github.com/kaaass/ZeroTierOne/releases/tag/mkmoonworld-1.0) 制作成目前的patch（集成planet和moon）。

# 必要条件

- 具有公网ip的服务器
- 安装 docker
- 安装 docker-compose
- 防火墙开放TCP端口 4000/9993/3180 和UDP端口 9993

# 用法

```
git clone https://github.com/Jonnyan404/zerotier-planet
OR
git clone https://gitee.com/Jonnyan404/zerotier-planet

cd zerotier-planet
docker-compose up -d
# 以下步骤为创建planet和moon
docker cp mkmoonworld-x86_64 ztncui:/tmp
docker cp patch.sh ztncui:/tmp
docker exec -it ztncui bash /tmp/patch.sh
docker restart ztncui
```

然后浏览器访问 `http://ip:4000` 打开web控制台界面。

浏览器访问 `http://ip:3180` 打开planet和moon文件下载页面（亦可在项目根目录的`./ztncui/etc/myfs/`里获取）。


- 用户名:admin
- 密码:mrdoc.fun

# 各客户端配置planet

限于篇幅，请到 <https://www.mrdoc.fun/doc/443/> 查阅



### 私有 zerotier-planet 的优势:
- 解除官方 25 的设备连接数限制
- 提升手机客户端连接的稳定性

# 同类型项目推荐

https://github.com/xubiaolin/docker-zerotier-planet

# Reference Link

- <https://www.mrdoc.fun/doc/443/>
- <https://github.com/key-networks/ztncui-aio>
