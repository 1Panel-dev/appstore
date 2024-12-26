# 使用说明

## 1. 准备域名证书
使用`acme.sh`、`certbot`、手动上传等方式准备好域名证书，注意需要按需修改。

证书对应域名为邮箱`MX`主机名如`mail.example.com `。

## 2. 安装应用

应用商店安装应用，

第一次安装会显示异常，容器无法正常运行，不必在意，因为缺少证书文件。

忽略错误，进行下一步操作。


## 3. 域名证书存放到存储卷

如果 容器-存储卷 目录没有  maddydata ，那么需要手动执行 
```
docker volume create maddydata 
```

存储卷默认路径如下
`/var/lib/docker/volumes/maddydata/_data/`

```
# 进入存储卷路径
cd $(docker volume inspect maddydata --format '{{.Mountpoint}}')

# 创建证书文件夹
mkdir -p tls
```
上传证书和私钥到`tls`文件夹，并重命名为
- fullchain.pem
- privkey.pem

按要求上传完成证书文件后，容器会自动正常运行。

## 4. 设置DKIM DNS解析
### 4.1 获取DKIM值

当容器正常运行后

在`/var/lib/docker/volumes/maddydata/_data/dkim_keys`路径下

会有个类似`example.com_default.dns`的文件

其中则是需要获取的相关信息。

- 注意按需修改域名

终端查看
```
cat /var/lib/docker/volumes/maddydata/_data/dkim_keys/example.com_default.dns 
```

会得到类似以下内容
```
default._domainkey.example.org.    TXT   "v=DKIM1; k=ed25519; p=nAcUUozPlhc4VPhp7hZl+owES7j7OlEv0laaDEDBAqg="
```

### 4.2 设置DNS TXT记录

根据获取的信息设置`DNS解析`

例子如下：
为 `default._domainkey.example.com` 添加`TXT`记录，值设置为`v=DKIM1; k=ed25519; p=nAcUUozPlhc4VPhp7hZl+owES7j7OlEv0laaDEDBAqg=`。

## 5. 设置DNS解析

- 注意按需修改

| 记录类型 | 域名  | 值   |
| --- | --- | --- |
| A   | `mail.example.com` | `服务器ipv4地址` |
| A   | `example.com` | `服务器ipv4地址` |
| AAAA | `mail.example.com` | `服务器ipv6地址（如果有）` |
| AAAA | `example.com` | `服务器ipv6地址（如果有）` |
| MX  | `example.com` | `mail.example.com` |
| TXT | `mail.example.com` | `v=spf1 mx ~all` |
| TXT | `example.com` | `v=spf1 mx ~all` |
| TXT | `_dmarc.example.com` | `v=DMARC1; p=quarantine; ruf=mailto:postmaster@example.com` |
| TXT | `_mta-sts.example.com` | `v=STSv1; id=1` |
| TXT | `_smtp._tls.example.com` | `v=TLSRPTv1;rua=mailto:postmaster@example.com` |

## 6. 创建发送账户

面板`容器`界面，连接容器终端，执行以下命令

- 注意按需修改

```
maddy creds create postmaster@example.com

maddy imap-acct create postmaster@example.com 
```
结束
