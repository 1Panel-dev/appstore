## 四台 GB10 部署 DeepSeek-V4.1-Flash

本版本使用售前提供的 1pds41 运行脚本和本地镜像 `eugr/spark-vllm-b12x:latest`。
应用版本中的 `2026.11.02` 表示镜像构建日期。
vLLM 完整运行版本为 `0.1.dev21830+g8244d1c44.d20261102`，
对应 `local-inference-lab/vllm` 提交 `8244d1c44348ae8a5bbfc9f1b607021652283ed4`。
仅适配镜像名称和模型宿主路径变量，原始脚本执行逻辑和推理参数保持不变。
运行文件校验清单位于 `scripts/source.sha256`。

部署前，四台设备均需准备相同的实测镜像，以及位于相同宿主路径的完整模型目录。
`DSPARK_MODEL_HOST` 默认为 `/opt/deepseek-ai/DeepSeek-V4.1-Flash`。
本应用不下载、构建或分发镜像和模型。
在主节点的 `.env` 中核对四节点地址、网卡、RoCE 设备和容器名，
并确保执行脚本的用户可以免密 SSH 到其余三台设备并操作 Docker。
1Panel 以 root 执行时，需要准备 root 用户的免密 SSH。

外层脚本通过 `APP_DIR` 定位应用目录，通过 `ENV_FILE` 读取该目录的 `.env`。
`IMAGE_NAME` 为镜像配置，默认 `eugr/spark-vllm-b12x:latest`；初始化检查、Compose 元数据
和原始启动脚本使用同一值。
`DSPARK_MODEL_HOST` 为模型宿主目录，初始化检查和四节点只读挂载使用同一值，
容器内路径仍为 `/models/DeepSeek-V4.1-Flash`。
目录须使用不含空格、冒号或 Shell 特殊字符的绝对路径，符合原始启动器的路径格式要求。

默认主节点为 `10.0.1.1`，其余节点为 `10.0.1.2`、`10.0.1.3`、`10.0.1.4`。
API 端口固定为 `8000`，模型名称为 `DeepSeek-V4.1-Flash`。

在主节点应用目录执行 `bash scripts/init.sh` 后，使用以下生命周期入口：

```bash
bash scripts/start.sh
bash scripts/stop.sh
bash scripts/restart.sh
```

启动入口在后台执行原始 `appliance/start.sh`，等待健康检查通过后返回。
运行日志位于 `logs/appliance.log`。停止和卸载入口调用原始 `appliance/stop.sh`，
并核验四节点的目标容器均已停止。

本次仅提供应用包。面板四机版本识别及生命周期调用需另行适配；
Compose 文件仅保留应用元数据，不能用 `docker compose up` 代替上述入口。
