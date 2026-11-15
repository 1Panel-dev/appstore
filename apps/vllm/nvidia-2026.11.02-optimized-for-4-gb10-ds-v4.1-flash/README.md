## 四台 GB10 部署 DeepSeek-V4.1-Flash

在四台 NVIDIA GB10 设备上部署 DeepSeek-V4.1-Flash，使用 B12X 推理后端和 DSpark 推测解码。
默认镜像为 `eugr/spark-vllm-b12x:latest`，主节点提供 OpenAI 兼容 API，其余三个节点参与分布式推理。

### 部署要求

- 四台设备均已安装 Docker、Docker Compose v2，并支持在容器中使用 NVIDIA GPU。
- 四台设备在相同的宿主机路径下存放完整且一致的模型文件。
- 节点间通信网络和 RoCE 接口已配置完成。
- 主节点运行 Linux，已安装 Python 3.10+、SSH 客户端和 `flock`，并可免密 SSH 到其他三台设备操作 Docker。通过 1Panel 部署时，需配置 root 用户的免密 SSH。

模型文件和网络需提前准备。镜像可按拉取策略由各节点自行下载；离线部署时，需预先在四台设备导入相同镜像。

### 集群配置

在应用目录的 `.env` 中配置以下参数：

| 变量 | 默认值 | 说明 |
| --- | --- | --- |
| `CLUSTER_NODES` | `10.0.1.1,10.0.1.2,10.0.1.3,10.0.1.4` | 按 rank 顺序排列的四个节点 IPv4 地址 |
| `LOCAL_IP` | `10.0.1.1` | 主节点地址，必须与节点列表首项一致 |
| `ETH_IF` | `enp1s0f0np0` | 节点间通信的网络接口 |
| `IB_IF` | `rocep1s0f0,roceP2p1s0f0` | NCCL 使用的 RoCE 设备 |
| `MASTER_PORT` | `29501` | 分布式协调端口 |

四个节点地址必须互不相同。首个节点为 rank 0，其余依次为 rank 1、2、3，使用 `--headless` 模式，不提供 HTTP 服务。
接口名称应按实际设备配置；使用双 RoCE 接口时，需为两个接口分别配置地址。
配置 `CLUSTER_LINKS` 时，启动脚本会校验拓扑和节点顺序，并为环形拓扑设置所需的 NCCL 环境变量。

### 应用配置

在 1Panel 表单中设置镜像、模型目录、端口和 API Key。这些值会写入应用配置，并用于四节点启动。
下表默认值来自应用包；通过面板部署时，以表单及生成的配置为准。

| 变量 | 默认值 | 说明 |
| --- | --- | --- |
| `DSPARK_VLLM_IMAGE` | `eugr/spark-vllm-b12x:latest` | 四节点容器镜像 |
| `DSPARK_MODEL_HOST` | `/opt/deepseek-ai/DeepSeek-V4.1-Flash` | 四节点模型宿主目录 |
| `DSPARK_MODEL_CONTAINER` | `/models` | 容器内只读模型挂载目录 |
| `VLLM_PORT` | `8000` | 主节点 API 端口，同时用于健康检查 |
| `VLLM_API_KEY` | 空 | API 访问密钥，空值不启用鉴权 |
| `SERVED_MODEL_NAME` | `DeepSeek-V4.1-Flash` | API 对外提供的模型名称 |
| `CONTAINER_NAME` | `vllm_node` | 容器名称，面板部署时自动生成 |
| `WORKER_DIR` | `/opt/1pds41` | 工作节点配置根目录，其下按项目名隔离 |
| `DSPARK_RESTART_POLICY` | `no` | 容器重启策略，默认不自动重启 |
| `WAIT_ATTEMPTS` | `100` | 启动就绪检查次数，须为正整数 |
| `WAIT_SECONDS` | `15` | 每次就绪检查失败后的等待秒数，须为正整数 |

模型目录须为不含空格、冒号或 Shell 特殊字符的绝对路径。容器使用 host 网络，API 直接监听主节点的 `VLLM_PORT`。

镜像拉取策略由 `IMAGE_PULL_POLICY` 控制：

- `if_not_present`（默认）：本地缺少镜像时拉取，对应面板未勾选“拉取镜像”。
- `always`：每次启动前在四台设备分别拉取，对应面板勾选“拉取镜像”。
- `never`：仅使用本地镜像，任一节点缺少镜像即失败。

各节点使用自身的 Docker 登录凭据和网络配置拉取镜像。启动前会校验四节点镜像一致性；拉取或校验失败时不会创建推理容器。

### 推理参数与环境变量

启动命令、GPU、缓存挂载、通信环境和健康检查统一在 `docker-compose.yml` 中定义。
默认使用 TP=4、7 个推测 token、最大并发序列数 4；需要调整时，修改 Compose 的 `command`。
`START_COMMAND`、`MTP_NUM_TOKENS` 和 `MAX_NUM_SEQS` 不控制本版本的启动参数。

Compose 中已定义的环境变量优先。`.env` 中的 `CONTAINER_*` 会去掉前缀后补充到容器环境，但不会覆盖 Compose 已定义的同名变量。
Compose 显式引用 `${CONTAINER_*}` 的配置从 `.env` 读取对应值。

### 启动、停止与卸载

1Panel 安装时自动准备配置并执行初始化和启动脚本。手动操作时，在应用目录按需执行：

| 操作 | 命令 |
| --- | --- |
| 初始化 | `bash scripts/init.sh` |
| 启动 | `bash scripts/start.sh` |
| 停止 | `bash scripts/stop.sh` |
| 重启 | `bash scripts/restart.sh` |
| 停止并清理远端部署配置 | `bash scripts/uninstall.sh` |

首次手动启动前需执行初始化。脚本与面板使用同一份 `.env` 和 Compose 配置；单独执行 `docker compose up` 只操作当前节点，不能完成四节点部署。

启动时先启动三个工作节点，再启动主节点，等待容器健康检查通过后返回。
主节点通过 `/health` 检查服务状态，工作节点不检查 HTTP 端口；工作节点的健康状态不能单独证明模型已就绪。
默认最多检查 100 次，每次失败后等待 15 秒，检查耗时另计。
面板启动和重启的执行时限为一小时，停止和卸载为十分钟；手动脚本没有整体时限，但各子命令有独立超时。

启动失败会输出日志并保留已启动的容器。排查后执行停止或重启操作。
可通过面板容器日志，或在对应节点执行 `docker logs -f <容器名>` 查看日志。

卸载脚本先停止全部四个节点，再删除三个工作节点上本项目的 `${WORKER_DIR}/${PROJECT_NAME}/docker-compose.json`，保留模型、缓存和其他文件。
停止或远端清理失败时，脚本返回失败。手动执行卸载脚本不会删除面板中的应用记录。

修改节点地址、项目名、容器名或工作节点配置目录前，应先使用原配置停止集群；如需清理旧的远端配置，应先执行卸载脚本。
