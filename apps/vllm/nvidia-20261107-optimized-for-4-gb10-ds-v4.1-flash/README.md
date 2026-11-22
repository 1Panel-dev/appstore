## 四台 GB10 部署 DeepSeek-V4.1-Flash-1017

本版本将 `deploy-package` 的 `spark-ds41f:1107` 配置接入 1Panel 四节点生命周期管理。
应用版本 `20261107` 对应上游 `spark-ds41f:1107` 发布标识，采用 `YYYYMMDD` 日期格式，不代表 vLLM 官方版本号。同一日期版本的打包修订使用 `.1`、`.2` 等递增后缀。
模型为 `DeepSeek-V4.1-Flash-1017`，使用 TileLang、direct 权重加载和 DSpark 推测解码。
镜像来自 [Christopher Owen 的 spark-ds41f](https://github.com/christopherowen/spark-ds41f)，部署包参考了 [J-M-Recipes PR #84](https://github.com/J-M-Recipes/recipes/pull/84)。

### 部署要求

- 四台 DGX Spark GB10，Linux ARM64、Docker、Docker Compose v2、NVIDIA 容器运行时、Python 3.10+；主节点另需 SSH 客户端和 `flock`。
- 主节点 root 用户可免密 SSH 到其余节点，主机密钥已受信任。
- 四台机器连接 200G RoCEv2 交换机，MTU 9000，每个节点的两个 HCA 功能配置在不同子网，接口名和 GID 配置一致。此版本不支持直连环形、三角形或 `CLUSTER_LINKS`。
- 四节点在同一绝对路径准备完整的 `DeepSeek-V4.1-Flash-1017`，包含 `config.json`、权重索引、全部索引引用的分片和 `encoding/`。模型目录以只读方式挂载到 `/models` 和 `/blobs`。
- 每节点启动前至少有 100 GiB 可用内存，没有其他 GPU 计算进程，具备 `/dev/infiniband` 及配置的 DRI 设备。

原部署包记录的宿主机环境为驱动 `580.178.04`、内核 `7.0.0-1025-nvidia`、4 KiB 页；本应用不修改内核、驱动或网络。
原包 README 的测量针对原版 checkpoint，不能作为 1017 的验证结果。本版本的 1017 推理、性能和硬件兼容性需在目标集群验证。

### 镜像准备

默认镜像为 `1panel/vllm:20261107-optimized-for-4-gb10-ds-v4.1-flash`，由每个节点根据 `IMAGE_PULL_POLICY` 自行处理：

- `if_not_present`（默认）：本地没有镜像时拉取，对应面板未勾选“拉取镜像”。
- `always`：在需要启动的节点拉取，对应面板勾选“拉取镜像”；已运行节点不会拉取，更新整个集群的镜像请执行重启。
- `never`：只使用本地镜像，任一节点缺失即失败。

该 1Panel 镜像尚待发布，应用目录和默认镜像名称的调整不代表镜像已可拉取。发布前使用已有镜像时，将 `DSPARK_VLLM_IMAGE` 设置为实际存放该 TileLang 镜像的完整仓库地址及 tag；使用原包本地标签时，在四台节点预先导入 `spark-ds41f:1107`，将 `DSPARK_VLLM_IMAGE` 改为该标签，并设置 `IMAGE_PULL_POLICY=never`。

主节点和三个工作节点并发准备镜像，各自使用自己的 Docker 登录凭据和网络配置拉取。脚本不从主节点传输镜像；等待全部节点完成后检查 Linux ARM64 平台和四节点镜像一致性，任一节点失败时不会创建推理容器。不再强制匹配原部署包的固定镜像 ID；ID 不同时按镜像层和运行配置比较内容。

本应用不包含镜像归档、模型或 JIT 缓存。离线部署获得原包归档及校验文件后，在每台节点执行：

```bash
sha256sum -c image.sha256
set -o pipefail
zstd -dc spark-ds41f-1107.tar.zst | docker load
```

模型文件仍需提前准备。镜像拉取单节点超时为 3000 秒，镜像检查和其他普通子命令默认超时为 60 秒。面板启动和重启的总执行时限为一小时，包含镜像准备、预检和模型启动等待，停止和卸载为十分钟。

### 配置

应用表单提供镜像、模型目录、端口和 API Key，其余配置在应用目录 `.env` 中设置。

| 配置 | 默认值 | 含义 |
| --- | --- | --- |
| `CLUSTER_NODES` | `10.0.1.1,10.0.1.2,10.0.1.3,10.0.1.4` | 按 rank 顺序排列的四个不同 IPv4 地址 |
| `LOCAL_IP` | `10.0.1.1` | 主节点，必须是节点列表第一项 |
| `ETH_IF` | `enp1s0f0np0` | 主通信接口 |
| `IB_IF` | `rocep1s0f0,roceP2p1s0f0` | 两个 RoCE HCA，以逗号分隔 |
| `GID_INDEX` | `3` | RoCEv2 GID 索引 |
| `DRI_CARD` | `/dev/dri/by-path/pci-000f:01:00.0-card` | 宿主机 DRI 设备路径 |
| `MASTER_PORT` | `9999` | 分布式协调端口 |
| `DSPARK_VLLM_IMAGE` | `1panel/vllm:20261107-optimized-for-4-gb10-ds-v4.1-flash` | 镜像仓库地址及 tag，也可使用预先导入的本地标签 |
| `DSPARK_MODEL_HOST` | `/opt/1panel/ai/models/deepseek-ai/DeepSeek-V4.1-Flash-1017` | 四节点模型目录 |
| `DSPARK_MODEL_CONTAINER` | `/models` | 容器内模型目录 |
| `SERVED_MODEL_NAME` | `DeepSeek-V4.1-Flash-1017` | API 模型别名 |
| `VLLM_PORT` | `8000` | 主节点 API 端口 |
| `VLLM_API_KEY` | 空 | 非空时启用 API 鉴权 |
| `CONTAINER_NAME` | `vllm_node` | 面板部署时自动生成 |
| `WORKER_DIR` | `/opt/1pds41-tilelang` | 工作节点配置根目录，按项目名隔离 |
| `CACHE_ROOT` | `/opt/1pds41-tilelang/cache` | 缓存根目录，按容器名隔离 |
| `WAIT_ATTEMPTS` / `WAIT_SECONDS` | `200` / `15` | 就绪检查次数与失败后的等待间隔 |

节点地址、接口和设备路径需按实际机器修改。模型、设备、缓存路径使用不含空格或 Shell 特殊字符的绝对路径。
主节点监听 `0.0.0.0`，使用 host 网络；API 与协调端口不可相同或已被占用。

应用商店表单和 `.env` 使用相同的默认镜像，模型别名默认为 `DeepSeek-V4.1-Flash-1017`。

### 推理配置

`docker-compose.yml` 是运行参数的唯一来源，沿用部署包的调优参数，并将最大并发序列统一调整为 8：

- TP=4，最大上下文 1,048,576，最大并发序列 8，batch token 预算 8192。
- 每 rank KV 缓存 11,274,289,152 bytes，GPU 内存利用率 0.82。
- TileLang attention/linear/MoE，DSpark 5 个推测 token、greedy draft、block rejection。
- `oneshot-switched` 通信，两个 HCA 和相同 GID 同时用于 NCCL 与 sparknet。
- 每容器内存及含 swap 总额度均为 112 GiB，CPU 绑定为 `5-9,15-19`。
- 默认关闭 thinking，每请求最多 4 张图片，容器不自动重启。

本版本不使用旧四机应用的 B12X 参数，也不根据 `MTP_NUM_TOKENS`、`MAX_NUM_SEQS` 或 `START_COMMAND` 改写启动命令。
镜像默认入口保持不变，Compose 的 `command` 使用多行写法，每个选项和值占一行；Compose 渲染为参数列表后，脚本仅给 rank 1–3 附加 `--headless`。
`CONTAINER_*` 环境变量去掉前缀后补充到容器，不覆盖 Compose 已定义的同名变量。

### 生命周期

| 操作 | 命令 |
| --- | --- |
| 初始化 | `bash scripts/init.sh` |
| 启动 | `bash scripts/start.sh` |
| 停止 | `bash scripts/stop.sh` |
| 重启 | `bash scripts/restart.sh` |
| 停止并清理远端配置 | `bash scripts/uninstall.sh` |

面板安装时调用初始化及启动脚本；手动首次启动也需先初始化。单独 `docker compose up` 只操作本机，不能完成集群部署。
脚本检查镜像、模型分片是否存在、设备、主接口 MTU、GID、可用内存、GPU 占用及主节点端口；不会验证所有权重文件的哈希，也不能替代跨节点 RoCE 连通性测试。
配置校验通过后，先在全部四个节点创建 `${CACHE_ROOT}/${CONTAINER_NAME}` 缓存目录；任一节点创建失败时，不启动容器。随后先启动三个工作节点，再启动主节点，等待主节点 `/health` 返回成功且工作节点仍运行。
首次冷启动需要编译内核，默认每 15 秒检查一次，最多 200 次，等待约 50 分钟，检查本身耗时另计。API Key 不影响 `/health` 检查。
配套 1Panel 的 GB10 启动、重启任务总超时为 2 小时，包含镜像拉取、部署及就绪等待；需同步更新后端，旧版后端仍受 1 小时上限约束。

启动时跳过已运行的本应用容器，只对未运行节点进行资源预检、写入配置并启动；已运行节点按容器实际镜像参与四节点镜像一致性校验。启动后仍检查全部四节点的就绪状态，不会自动停止或重建已运行节点。容器处于暂停或重启中时提示异常。修改配置或统一更新镜像请执行重启。

部分节点退出后，仅补启动退出节点不保证原有分布式通信恢复；若就绪检查失败，应排查后执行重启，统一停止并启动四节点。启动失败保留容器并输出本次启动节点的近期日志。停止和卸载只处理有当前 Compose 项目及服务标签的容器，同名外部容器会被拒绝操作。
卸载删除工作节点上当前项目的 Compose 配置，保留模型、镜像与缓存。缓存位于各节点 `${CACHE_ROOT}/${CONTAINER_NAME}`，重启不会清空。
主机重启后等待四节点恢复，再通过重启操作协调拉起。修改节点地址、项目名、容器名或工作目录前，先使用旧配置停止并清理旧部署。

### 来源与许可

应用生命周期脚本沿用现有四 GB10 应用，保留其 MIT `LICENSE`。
镜像及运行参数来源为 [spark-ds41f](https://github.com/christopherowen/spark-ds41f)（Apache-2.0）；镜像内依赖遵循各自上游许可证。本应用不包含上游源码或模型权重。
