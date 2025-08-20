## 产品介绍

**Synapse** 是由 Matrix.org 基金会编写和维护的开源Matrix家庭服务器。

## 使用说明

- 创建前需要使用终端运行以下命令创建依赖配置文件，需要按需修改参数 `my.matrix.host`

```shell
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

> 配置文件默认存放路径是在 `synapse-data` 存储卷里，`/var/lib/docker/volumes/synapse-data/_data`

### 创建应用

### 创建用户

- 创建管理员账户

```shell
register_new_matrix_user  http://localhost:8008 -c /data/homeserver.yaml  -a -u 管理员用户名 -p 密码
```

- 创建普通用户账户

```shell
register_new_matrix_user  http://localhost:8008 -c /data/homeserver.yaml   --no-admin -u 普通用户名 -p 密码
```

- 查看更多命令与帮助

```shell
register_new_matrix_user http://localhost:8008 -c /data/homeserver.yaml --help
```

### 提示

所有数据存放在 `synapse-data` 存储卷里，删除应用时，假如需要完全清除数据，还需要将 `synapse-data` 存储卷删除。
