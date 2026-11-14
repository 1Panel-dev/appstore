## 四台 GB10 部署 DeepSeek-V4.1-Flash

本版本使用售前提供的 1pds41 运行脚本和镜像 `eugr/spark-vllm-b12x:latest`。
应用版本中的 `2026.11.02` 表示镜像构建日期。
vLLM 完整运行版本为 `0.1.dev21830+g8244d1c44.d20261102`，
对应 `local-inference-lab/vllm` 提交 `8244d1c44348ae8a5bbfc9f1b607021652283ed4`。
沿用 1pds41 四机编排及 B12X/DSpark 推理参数，应用配置统一使用现有 GB10 的环境变量。
运行文件校验清单位于 `scripts/source.sha256`。

部署前，四台设备均需在相同宿主路径准备完整模型，并使用相同镜像。
`DSPARK_MODEL_HOST` 默认为 `/opt/deepseek-ai/DeepSeek-V4.1-Flash`。
镜像按 `IMAGE_PULL_POLICY` 在各节点准备；本应用不构建或分发镜像，也不下载或复制模型。
在主节点的 `.env` 中核对四节点地址、网卡、RoCE 设备和容器名，
并确保执行脚本的用户可以免密 SSH 到其余三台设备并操作 Docker。
1Panel 以 root 执行时，需要准备 root 用户的免密 SSH。

所有入口通过 `scripts/config.sh` 读取应用目录的 `.env`，以该文件为配置来源。
修改 `.env` 后，直接执行 `appliance/start.sh` 与通过 `scripts/start.sh` 启动使用同一组配置。

| 变量 | 默认值 | 用途 |
| --- | --- | --- |
| `DSPARK_VLLM_IMAGE` | `eugr/spark-vllm-b12x:latest` | 镜像检查和四节点启动 |
| `IMAGE_PULL_POLICY` | `if_not_present` | 与 GB10 表单共用的镜像拉取策略 |
| `DSPARK_MODEL_HOST` | `/opt/deepseek-ai/DeepSeek-V4.1-Flash` | 四节点模型宿主目录 |
| `DSPARK_MODEL_CONTAINER` | `/models` | 只读挂载目标和 vLLM 模型路径 |
| `CONTAINER_NAME` | `vllm_node` | 四节点容器名称及停止、健康检查 |
| `VLLM_PORT` | `8000` | API 监听端口、端口占用检查和健康检查 |
| `VLLM_API_KEY` | 空 | 通过容器环境传递，并作为 vLLM 的 API Key 参数；空值不启用鉴权 |
| `SERVED_MODEL_NAME` | `DeepSeek-V4.1-Flash` | OpenAI API 模型名称 |

镜像拉取策略：

- `always`：每次启动前，在四台设备上分别拉取指定镜像，对应面板勾选“拉取镜像”。
- `if_not_present`：每台设备优先使用本地镜像，仅在缺少镜像时拉取，对应面板未勾选“拉取镜像”。
- `never`：只使用预先导入的本地镜像，适用于离线部署；任一节点缺少镜像即失败。

拉取使用各节点自身的 Docker 登录凭据及网络配置。全部准备成功后，仍会校验各节点镜像内容一致；
拉取失败或镜像不一致时，不启动推理容器。使用未发布到镜像仓库的本地镜像时，应预先导入四台设备。
直接执行脚本与面板生命周期入口使用相同策略。

`data.yml` 的镜像、模型宿主目录、端口和 API Key 表单直接写入上述同名变量。
与现有 GB10 表单对接时，继续使用其 `DSPARK_*`、`VLLM_PORT` 和 `VLLM_API_KEY` 参数；
`PANEL_APP_PORT_HTTP` 是面板端口元数据，实际服务端口以 `VLLM_PORT` 为准，两者应由面板保持一致。
旧包的 `IMAGE_NAME`、`MODEL_CONTAINER_PATH` 不再作为应用配置入口，更新已有配置时应迁移到上述变量。
配置缺失或端口、路径不合法时会在启动前报错。

容器内模型路径默认统一为 `/models`，挂载和推理命令同时读取 `DSPARK_MODEL_CONTAINER`；
模型内容与原脚本不变。模型目录须使用不含空格、冒号或 Shell 特殊字符的绝对路径，
符合原始启动器的路径格式要求。四机专用推理参数仍保留在 `appliance/deepseek-v4.1-flash.sh` 中，
不会读取旧两机版本的 `START_COMMAND`、`MTP_NUM_TOKENS` 或 `MAX_NUM_SEQS`。

默认主节点为 `10.0.1.1`，其余节点为 `10.0.1.2`、`10.0.1.3`、`10.0.1.4`。
保留 `.env` 中的 `CLUSTER_NODES`、`LOCAL_IP`、`ETH_IF`、`IB_IF`、`MASTER_PORT` 及
`CONTAINER_*` 运行环境配置。额外容器环境仍使用 `CONTAINER_变量名` 形式；
单独修改 Compose 的 `environment` 不会修改脚本创建的容器。

在主节点应用目录执行 `bash scripts/init.sh` 后，使用以下生命周期入口：

```bash
bash scripts/start.sh
bash scripts/stop.sh
bash scripts/restart.sh
```

启动入口在后台执行 `appliance/start.sh`，等待健康检查通过后返回。
运行日志位于 `logs/appliance.log`。停止和卸载入口调用 `appliance/stop.sh`，
并核验四节点的目标容器均已停止。

本次仅提供应用包。面板四机版本识别及生命周期调用需另行适配；
Compose 文件仅保留应用元数据，不能用 `docker compose up` 代替上述入口。
