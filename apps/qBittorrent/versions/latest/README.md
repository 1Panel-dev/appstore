
# 本应用介绍

基于镜像
> - https://hub.docker.com/r/jarylc/qbittorrent

- 数据目录："./data"
- 默认账号：admin
- 密码：adminadmin
- 开启中文，默认为PT下载做的设置，但未优化参数。

> 原项目相关
>> - https://www.qbittorrent.org/
>> - https://github.com/qbittorrent/qBittorrent

# 原项目介绍

- qBittorrent是一个跨平台的开源、自由的BitTorrent客户端，其图形用户界面是通过Qt所写，后端使用libtorrent。
- 由贝尔福- 蒙贝利亚尔理工大学的克里斯托夫·迪梅，在2006年3月开始开发。

qBittorrent - A BitTorrent client in Qt
------------------------------------------

[![AppVeyor Status](https://ci.appveyor.com/api/projects/status/github/qbittorrent/qBittorrent?branch=master&svg=true)](https://ci.appveyor.com/project/qbittorrent/qBittorrent)
[![GitHub Actions CI Status](https://github.com/qbittorrent/qBittorrent/workflows/GitHub%20Actions%20CI/badge.svg)](https://github.com/qbittorrent/qBittorrent/actions)
[![Coverity Status](https://scan.coverity.com/projects/5494/badge.svg)](https://scan.coverity.com/projects/5494)
********************************
### Description:
qBittorrent is a bittorrent client programmed in C++ / Qt that uses
libtorrent (sometimes called libtorrent-rasterbar) by Arvid Norberg.

It aims to be a good alternative to all other bittorrent clients
out there. qBittorrent is fast, stable and provides unicode
support as well as many features.

The free [IP to Country Lite database](https://db-ip.com/db/download/ip-to-country-lite) by [DB-IP](https://db-ip.com/) is used for resolving the countries of peers. The database is licensed under the [Creative Commons Attribution 4.0 International License](https://creativecommons.org/licenses/by/4.0/).

### Installation:
For installation, follow the instructions from INSTALL file, but simple:

```
./configure
make && make install
qbittorrent
```

will install and execute qBittorrent hopefully without any problem.

### Public key:
Starting from v3.3.4 all source tarballs and binaries are signed.<br />
The key currently used is 4096R/[5B7CC9A2](https://pgp.mit.edu/pks/lookup?op=get&search=0x6E4A2D025B7CC9A2) with fingerprint `D8F3DA77AAC6741053599C136E4A2D025B7CC9A2`.<br />
You can also download it from [here](https://github.com/qbittorrent/qBittorrent/raw/master/5B7CC9A2.asc).<br />
**PREVIOUSLY** the following key was used to sign the v3.3.4 source tarballs and v3.3.4 Windows installer **only**: 4096R/[520EC6F6](https://pgp.mit.edu/pks/lookup?op=get&search=0xA1ACCAE4520EC6F6) with fingerprint `F4A5FD201B117B1C2AB590E2A1ACCAE4520EC6F6`.<br />

### Misc:
For more information please visit:
https://www.qbittorrent.org

or our wiki here:
http://wiki.qbittorrent.org

Use the forum for troubleshooting before reporting bugs:
http://forum.qbittorrent.org

Please report any bug (or feature request) to:
http://bugs.qbittorrent.org

Official IRC channel:
[#qbittorrent on irc.libera.chat](ircs://irc.libera.chat:6697/qbittorrent)

------------------------------------------
sledgehammer999 \<sledgehammer999@qbittorrent.org\>

