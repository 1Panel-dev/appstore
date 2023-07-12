# 使用说明

- 默认账户与密码(注意大小写)

```
username:Admin
password:zabbix
```

## 注意事项

**注意：这是Zabbix-MySQL版本的，Zabbix 6.X的需求环境MySQL8**

商店自带的`MySQL 8`的数据库格式设置与`Zabbix`需求有所不同，`zabbix-server-mysql`容器会提示存在错误。

但是实际能够运行。如有错误，期待反馈。

- 带`&mysql`版本，会安装符合`Zabbix`格式要求的数据库版本
- 不带`&mysql`的版本，默认调用面板安装的数据库


# 原始相关

Zabbix is free software, released under the GNU General Public License
(GPL) version 2.

You can redistribute it and/or modify it under the terms of the GNU GPL
as published by the Free Software Foundation; either version 2 of the
License, or (at your option) any later version.

The formal terms of the GPL can be found at
http://www.fsf.org/licenses/ .

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

Exception for linking with OpenSSL

In addition, as a special exception, we give permission to link the code
of Zabbix with the OpenSSL project OpenSSL library (or with modified
versions of it that use the same license as the OpenSSL library), and
distribute the linked executables.

Please see https://www.zabbix.com/ for detailed information about Zabbix.

On-line Zabbix documentation is available at
https://www.zabbix.com/documentation/6.2/manual/ .

Zabbix installation instructions can be found at
https://www.zabbix.com/documentation/6.2/manual/installation/ .

If you are installing Zabbix from packages the instructions can be found at
https://www.zabbix.com/documentation/6.2/manual/installation/install_from_packages/ .