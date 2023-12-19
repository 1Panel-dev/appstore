#  **[SpeedTest by OpenSpeedTest™](https://openspeedtest.com?Run&ref=Github)** - 免费且开源的HTML5网络性能估算工具。

> OpenSpeedTest™是一个免费且开源的HTML5网络性能估算工具，使用纯粹的JavaScript编写，只使用内置的Web API，如  `XMLHttpRequest` `(XHR)`, `HTML`, `CSS`, `JS`, 和 `SVG`. 。无需第三方框架或库。只需要一个静态Web服务器，如 `NGINX`。本项目启动于2011年，并于2013年迁移到OpenSpeedTest.com专用项目/域名。

## 主要功能
提供开箱即用的网络性能估算工具，无需任何配置即可使用。

## 使用
为了提供更易用的体验，本应用只提供了最基础的功能，如需更多功能请参考[文档](https://github.com/openspeedtest/Speed-Test) `编辑 compose 文件` 实现。

### 端口开放
默认端口为 HTTP/3000，HTTPS/3001

### HTTPS
若需要启用 HTTPS，建议搭配 `OpenResty` 反向代理使用，同时请参考 [openspeedtest/Nginx-Configuration](https://github.com/openspeedtest/Nginx-Configuration) 修改配置文件。