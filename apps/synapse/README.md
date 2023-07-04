# 使用说明

## 步骤1
创建前需要使用终端运行以下命令创建依赖配置文件，
需要按需修改参数`my.matrix.host`



```
# 参数解释说明
docker run -it --rm \
  -v synapse-data:/data \  # 挂载一个卷，将容器内的 /data 目录映射到 synapse-data 卷
  -e SYNAPSE_SERVER_NAME=my.matrix.host \  # 设置 Synapse 服务器的公共主机名
  -e SYNAPSE_REPORT_STATS=no \  # 禁用匿名统计报告
  -e SYNAPSE_HTTP_PORT=8008 \  # 设置 Synapse 监听的 HTTP 端口为 8008
  -e SYNAPSE_CONFIG_DIR=/data \  # 设置配置文件的存储位置为 /data
  -e SYNAPSE_DATA_DIR=/data \  # 设置持久数据的存储位置为 /data
  -e TZ=Asia/Shanghai \  # 设置容器的时区为亚洲/上海
  -e UID=1000 \  # 设置运行 Synapse 的用户 ID
  -e GID=1000 \  # 设置运行 Synapse 的用户组 ID
  matrixdotorg/synapse:latest generate  # 运行最新版本的 matrixdotorg/synapse 镜像，并执行 generate 命令来生成配置文件

```
实际运行命令，注意修改
```
docker run -it --rm \
  -v synapse-data:/data \
  -e SYNAPSE_SERVER_NAME=my.matrix.host \
  -e SYNAPSE_REPORT_STATS=no \
  -e SYNAPSE_HTTP_PORT=8008 \
  -e SYNAPSE_CONFIG_DIR=/data \
  -e SYNAPSE_DATA_DIR=/data \
  -e TZ=Asia/Shanghai \
  -e UID=1000 \
  -e GID=1000 \
  matrixdotorg/synapse:latest generate

```

配置文件默认存放路径是在一个`synapse-data`存储卷里，
```
# 配置文件路径
/var/lib/docker/volumes/synapse-data/_data
```

## 步骤2
创建应用

## 步骤3

需要打开容器，运行命令创建用户

```
# 创建管理员账户
# register_new_matrix_user  http://localhost:8008 -c /data/homeserver.yaml  -a -u 用户名 -p 密码
register_new_matrix_user  http://localhost:8008 -c /data/homeserver.yaml  -a -u admin -p password

# 创建普通用户账户
# register_new_matrix_user  http://localhost:8008 -c /data/homeserver.yaml   --no-admin -u 用户名 -p 密码
register_new_matrix_user  http://localhost:8008 -c /data/homeserver.yaml   --no-admin -u user -p password

# 查看更多命令与帮助
register_new_matrix_user http://localhost:8008 -c /data/homeserver.yaml --help
```

`register_new_matrix_user`自带命令

```
usage: register_new_matrix_user [-h] [-u USER] [-p PASSWORD] [-t USER_TYPE] [-a | --no-admin] (-c CONFIG | -k SHARED_SECRET) [server_url]
用法：register_new_matrix_user [-h] [-u USER] [-p PASSWORD] [-t USER_TYPE] [-a | --no-admin] (-c CONFIG | -k SHARED_SECRET) [server_url]

Used to register new users with a given homeserver when registration has been disabled. The homeserver must be configured with the 'registration_shared_secret' option set.
用于在注册被禁用时，通过给定的homeserver注册新用户。homeserver必须配置'registration_shared_secret'选项。

positional arguments:
位置参数：
server_url URL to use to talk to the homeserver. By default, tries to find a suitable URL from the configuration file. Otherwise, defaults to 'http://localhost:8008'.
server_url 与homeserver通信的URL。默认情况下，尝试从配置文件中找到合适的URL。否则，默认为'http://localhost:8008'。

options:
选项：
-h, --help show this help message and exit
-h, --help 显示帮助信息并退出
-u USER, --user USER Local part of the new user. Will prompt if omitted.
-u USER, --user USER 新用户的本地部分。如果省略，将提示输入。
-p PASSWORD, --password PASSWORD
New password for user. Will prompt if omitted.
-p PASSWORD, --password PASSWORD
用户的新密码。如果省略，将提示输入。
-t USER_TYPE, --user_type USER_TYPE
User type as specified in synapse.api.constants.UserTypes
-t USER_TYPE, --user_type USER_TYPE
用户类型，如synapse.api.constants.UserTypes中所指定的
-a, --admin Register new user as an admin. Will prompt if --no-admin is not set either.
-a, --admin 将新用户注册为管理员。如果未设置--no-admin，也会提示输入。
--no-admin Register new user as a regular user. Will prompt if --admin is not set either.
--no-admin 将新用户注册为普通用户。如果未设置--admin，也会提示输入。
-c CONFIG, --config CONFIG
Path to server config file. Used to read in shared secret.
-c CONFIG, --config CONFIG
服务器配置文件的路径。用于读取共享密钥。
-k SHARED_SECRET, --shared-secret SHARED_SECRET
Shared secret as defined in server config file.
-k SHARED_SECRET, --shared-secret SHARED_SECRET
服务器配置文件中定义的共享密钥。
```

## 提示

所有数据存放在`synapse-data`存储卷里，

删除应用时，假如需要完全清除数据，还需要将`synapse-data`存储卷删除。

# 原始相关
## Synapse 
[![(get support on #synapse:matrix.org)](https://img.shields.io/matrix/synapse:matrix.org?label=support&logo=matrix)](https://matrix.to/#/#synapse:matrix.org) [![(discuss development on #synapse-dev:matrix.org)](https://img.shields.io/matrix/synapse-dev:matrix.org?label=development&logo=matrix)](https://matrix.to/#/#synapse-dev:matrix.org) [![(Rendered documentation on GitHub Pages)](https://img.shields.io/badge/documentation-%E2%9C%93-success)](https://matrix-org.github.io/synapse/latest/) [![(check license in LICENSE file)](https://img.shields.io/github/license/matrix-org/synapse)](https://raw.githubusercontent.com/matrix-org/synapse/develop/LICENSE) [![(latest version released on PyPi)](https://img.shields.io/pypi/v/matrix-synapse)](https://pypi.org/project/matrix-synapse) [![(supported python versions)](https://img.shields.io/pypi/pyversions/matrix-synapse)](https://pypi.org/project/matrix-synapse)

Synapse is an open-source [Matrix](https://matrix.org/) homeserver written and maintained by the [Matrix.org](https://github.com/matrix-org/Matrix.org) Foundation. We began rapid development in 2014, reaching v1.0.0 in 2019. Development on Synapse and the Matrix protocol itself continues in earnest today.

Briefly, Matrix is an open standard for communications on the internet, supporting federation, encryption and VoIP. [Matrix.org](https://github.com/matrix-org/Matrix.org) has more to say about the [goals of the Matrix project](https://matrix.org/docs/guides/introduction), and the [formal specification](https://spec.matrix.org/) describes the technical details.

Contents

- <a id="user-content-id1"></a>[Installing and configuration](#installing-and-configuration)
    - <a id="user-content-id2"></a>[Using a reverse proxy with Synapse](#using-a-reverse-proxy-with-synapse)
    - <a id="user-content-id3"></a>[Upgrading an existing Synapse](#upgrading-an-existing-synapse)
    - <a id="user-content-id4"></a>[Platform dependencies](#platform-dependencies)
    - <a id="user-content-id5"></a>[Security note](#security-note)
- <a id="user-content-id6"></a>[Testing a new installation](#testing-a-new-installation)
    - <a id="user-content-id7"></a>[Registering a new user from a client](#registering-a-new-user-from-a-client)
- <a id="user-content-id8"></a>[Troubleshooting and support](#troubleshooting-and-support)
- <a id="user-content-id9"></a>[Identity Servers](#identity-servers)
- <a id="user-content-id10"></a>[Development](#development)

<a id="user-content-installing-and-configuration"></a>

## [Installing and configuration](#id1)

The Synapse documentation describes [how to install Synapse](https://matrix-org.github.io/synapse/latest/setup/installation.html). We recommend using [Docker images](https://matrix-org.github.io/synapse/latest/setup/installation.html#docker-images-and-ansible-playbooks) or [Debian packages from Matrix.org](https://matrix-org.github.io/synapse/latest/setup/installation.html#matrixorg-packages).

Synapse has a variety of [config options](https://matrix-org.github.io/synapse/latest/usage/configuration/config_documentation.html) which can be used to customise its behaviour after installation. There are additional details on how to [configure Synapse for federation here](https://matrix-org.github.io/synapse/latest/federate.html).

<a id="user-content-using-a-reverse-proxy-with-synapse"></a>

### [Using a reverse proxy with Synapse](#id2)

It is recommended to put a reverse proxy such as [nginx](https://nginx.org/en/docs/http/ngx_http_proxy_module.html), [Apache](https://httpd.apache.org/docs/current/mod/mod_proxy_http.html), [Caddy](https://caddyserver.com/docs/quick-starts/reverse-proxy), [HAProxy](https://www.haproxy.org/) or [relayd](https://man.openbsd.org/relayd.8) in front of Synapse. One advantage of doing so is that it means that you can expose the default https port (443) to Matrix clients without needing to run Synapse with root privileges. For information on configuring one, see [the reverse proxy docs](https://matrix-org.github.io/synapse/latest/reverse_proxy.html).

<a id="user-content-upgrading-an-existing-synapse"></a>

### [Upgrading an existing Synapse](#id3)

The instructions for upgrading Synapse are in [the upgrade notes](https://matrix-org.github.io/synapse/develop/upgrade.html). Please check these instructions as upgrading may require extra steps for some versions of Synapse.

<a id="user-content-platform-dependencies"></a>

### [Platform dependencies](#id4)

Synapse uses a number of platform dependencies such as Python and PostgreSQL, and aims to follow supported upstream versions. See the [deprecation policy](https://matrix-org.github.io/synapse/latest/deprecation_policy.html) for more details.

<a id="user-content-security-note"></a>

### [Security note](#id5)

Matrix serves raw, user-supplied data in some APIs -- specifically the [content repository endpoints](https://matrix.org/docs/spec/client_server/latest.html#get-matrix-media-r0-download-servername-mediaid).

Whilst we make a reasonable effort to mitigate against XSS attacks (for instance, by using [CSP](https://github.com/matrix-org/synapse/pull/1021)), a Matrix homeserver should not be hosted on a domain hosting other web applications. This especially applies to sharing the domain with Matrix web clients and other sensitive applications like webmail. See https://developer.github.com/changes/2014-04-25-user-content-security for more information.

Ideally, the homeserver should not simply be on a different subdomain, but on a completely different [registered domain](https://tools.ietf.org/html/draft-ietf-httpbis-rfc6265bis-03#section-2.3) (also known as top-level site or eTLD+1). This is because [some attacks](https://en.wikipedia.org/wiki/Session_fixation#Attacks_using_cross-subdomain_cookie) are still possible as long as the two applications share the same registered domain.

To illustrate this with an example, if your Element Web or other sensitive web application is hosted on `A.example1.com`, you should ideally host Synapse on `example2.com`. Some amount of protection is offered by hosting on `B.example1.com` instead, so this is also acceptable in some scenarios. However, you should *not* host your Synapse on `A.example1.com`.

Note that all of the above refers exclusively to the domain used in Synapse's `public_baseurl` setting. In particular, it has no bearing on the domain mentioned in MXIDs hosted on that server.

Following this advice ensures that even if an XSS is found in Synapse, the impact to other applications will be minimal.

<a id="user-content-testing-a-new-installation"></a>

## [Testing a new installation](#id6)

The easiest way to try out your new Synapse installation is by connecting to it from a web client.

Unless you are running a test instance of Synapse on your local machine, in general, you will need to enable TLS support before you can successfully connect from a client: see [TLS certificates](https://matrix-org.github.io/synapse/latest/setup/installation.html#tls-certificates).

An easy way to get started is to login or register via Element at https://app.element.io/#/login or https://app.element.io/#/register respectively. You will need to change the server you are logging into from `matrix.org` and instead specify a Homeserver URL of `https://<server_name>:8448` (or just `https://<server_name>` if you are using a reverse proxy). If you prefer to use another client, refer to our [client breakdown](https://matrix.org/docs/projects/clients-matrix).

If all goes well you should at least be able to log in, create a room, and start sending messages.

<a id="user-content-registering-a-new-user-from-a-client"></a>

### [Registering a new user from a client](#id7)

By default, registration of new users via Matrix clients is disabled. To enable it:

1.  In the [registration config section](https://matrix-org.github.io/synapse/latest/usage/configuration/config_documentation.html#registration) set `enable_registration: true` in `homeserver.yaml`.
2.  Then **either**:
    1.  set up a [CAPTCHA](https://matrix-org.github.io/synapse/latest/CAPTCHA_SETUP.html), or
    2.  set `enable_registration_without_verification: true` in `homeserver.yaml`.

We **strongly** recommend using a CAPTCHA, particularly if your homeserver is exposed to the public internet. Without it, anyone can freely register accounts on your homeserver. This can be exploited by attackers to create spambots targetting the rest of the Matrix federation.

Your new user name will be formed partly from the `server_name`, and partly from a localpart you specify when you create the account. Your name will take the form of:

@localpart:my.domain.name

(pronounced "at localpart on my dot domain dot name").

As when logging in, you will need to specify a "Custom server". Specify your desired `localpart` in the 'User name' box.

<a id="user-content-troubleshooting-and-support"></a>

## [Troubleshooting and support](#id8)

The [Admin FAQ](https://matrix-org.github.io/synapse/latest/usage/administration/admin_faq.html) includes tips on dealing with some common problems. For more details, see [Synapse's wider documentation](https://matrix-org.github.io/synapse/latest/).

For additional support installing or managing Synapse, please ask in the community support room [`#synapse:matrix.org`](https://matrix.to/#/#synapse:matrix.org) (from a [matrix.org](https://github.com/matrix-org/matrix.org) account if necessary). We do not use GitHub issues for support requests, only for bug reports and feature requests.

<a id="user-content-identity-servers"></a>

## [Identity Servers](#id9)

Identity servers have the job of mapping email addresses and other 3rd Party IDs (3PIDs) to Matrix user IDs, as well as verifying the ownership of 3PIDs before creating that mapping.

**They are not where accounts or credentials are stored - these live on home servers. Identity Servers are just for mapping 3rd party IDs to matrix IDs.**

This process is very security-sensitive, as there is obvious risk of spam if it is too easy to sign up for Matrix accounts or harvest 3PID data. In the longer term, we hope to create a decentralised system to manage it ([matrix-doc #712](https://github.com/matrix-org/matrix-doc/issues/712)), but in the meantime, the role of managing trusted identity in the Matrix ecosystem is farmed out to a cluster of known trusted ecosystem partners, who run 'Matrix Identity Servers' such as [Sydent](https://github.com/matrix-org/sydent), whose role is purely to authenticate and track 3PID logins and publish end-user public keys.

You can host your own copy of Sydent, but this will prevent you reaching other users in the Matrix ecosystem via their email address, and prevent them finding you. We therefore recommend that you use one of the centralised identity servers at `https://matrix.org` or `https://vector.im` for now.

To reiterate: the Identity server will only be used if you choose to associate an email address with your account, or send an invite to another user via their email address.

<a id="user-content-development"></a>

## [Development](#id10)

We welcome contributions to Synapse from the community! The best place to get started is our [guide for contributors](https://matrix-org.github.io/synapse/latest/development/contributing_guide.html). This is part of our larger [documentation](https://matrix-org.github.io/synapse/latest), which includes

information for Synapse developers as well as Synapse administrators. Developers might be particularly interested in:

- [Synapse's database schema](https://matrix-org.github.io/synapse/latest/development/database_schema.html),
- [notes on Synapse's implementation details](https://matrix-org.github.io/synapse/latest/development/internal_documentation/index.html), and
- [how we use git](https://matrix-org.github.io/synapse/latest/development/git.html).

Alongside all that, join our developer community on Matrix: [#synapse-dev:matrix.org](https://matrix.to/#/#synapse-dev:matrix.org), featuring real humans!
