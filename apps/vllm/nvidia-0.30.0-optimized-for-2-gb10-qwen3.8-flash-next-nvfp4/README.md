# Qwen3.8-Flash-Next NVFP4 双机 GB10

应用版本：`nvidia-0.30.0-optimized-for-2-gb10-qwen3.8-flash-next-nvfp4`。
本版本将 `qwen38-dual-share` 的推理配置与补丁接入 vLLM 应用，使用两个 GB10 节点，TP2 + EP + MTP3。

## 部署准备

- 两台 Linux ARM64 GB10（DGX Spark），每台约 128 GB 统一内存，提供可用的 RoCEv2 互联。
- 两端安装 Docker Compose v2、NVIDIA Container Toolkit、Python 3.10+、iproute2（提供 `ip` 和 `ss`），主节点另需 `flock`，worker 另需 GNU coreutils（提供 `timeout`）。
- 由主节点 root 执行生命周期脚本，提前配置到 worker 的免密 SSH，并确认 SSH 主机密钥。
- 两端准备同版本镜像 `vllm/vllm-openai:v0.30.0`。MTP 补丁绑定此版本，镜像字段仅用于填写相同镜像的仓库镜像地址，不应直接升级 vLLM。
- 两端各准备完整的 NVIDIA 官方 `nvidia/Qwen3.8-Flash-Next-NVFP4` 权重，默认路径为 `/opt/1panel/ai/models/nvidia/Qwen3.8-Flash-Next-NVFP4`。不支持直接替换为 RadixArk 版。

模型目录直接包含 `config.json`、tokenizer 文件、`model.safetensors.index.json`、索引引用的所有分片和 `model-fp8-mtp-ple.safetensors`，挂载到容器 `/models`。模型约 133 GB，不随应用分发、不自动下载。
从旧包 HF 缓存迁移时，将对应 snapshot 的文件解引用后复制到模型目录；不能把含外部相对软链接的 snapshot 单独挂载，否则容器无法读取 blobs。

## 配置

应用表单包含端口 `VLLM_PORT`（默认 8000）、镜像 `DSPARK_VLLM_IMAGE`、模型目录 `DSPARK_MODEL_HOST`、`VLLM_API_KEY`。API Key 留空时不鉴权。GID 默认在启动时自动识别，无需在表单中填写索引。
其余参数保存在应用版本 `.env`：

| 变量 | 含义 |
| --- | --- |
| `CLUSTER_NODES` | 两个不同的 IPv4 地址，以逗号分隔，第一项为 head |
| `LOCAL_IP` | head 的 RoCE 网络 IPv4 地址，必须等于第一项 |
| `ETH_IF` / `IB_IF` | head 网络接口和 HCA 名称；HCA 不带 `=` 前缀 |
| `NCCL_IB_GID_AUTO` | 默认 `1`，启动时自动识别 GID；`0` 使用手动索引 |
| `GID_INDEX` | head 手动 GID 索引，仅在自动识别关闭时生效 |
| `WORKER_ETH_IF` / `WORKER_IB_IF` / `WORKER_GID_INDEX` | worker 的对应网络配置，留空时使用 head 值 |
| `MASTER_PORT` | 双机通信端口，默认 50000，不能与 API 端口相同 |
| `CONTAINER_NAME` | 两端容器名，所有操作只管理当前应用所属容器 |
| `WORKER_DIR` | worker 配置与补丁目录的根路径，默认 `/opt/1pqw` |
| `CACHE_ROOT` | 两端缓存根路径，默认 `/opt/1pqw/cache`，按容器名隔离 |
| `IMAGE_PULL_POLICY` | `if_not_present`、`always` 或 `never`；离线可选 `never` |
| `WAIT_ATTEMPTS` / `WAIT_SECONDS` | 乘积为就绪等待的实际时间预算，默认 200 × 15 秒（50 分钟）；检查间隔为 `WAIT_SECONDS` |

GID 处理沿用 0.25 双机版本的规则：默认 `NCCL_IB_GID_AUTO=1`，每次启动分别在 head 和 worker 上读取通信接口 IPv4，扫描所选 HCA 的活动端口，跳过空槽及非 RoCE v2 项。优先匹配通信 IP，也允许匹配端口所属网络接口的 IPv4；同一节点选择多个端口时取可用索引的交集。找不到共同索引时明确报错，不继续启动。

可用 `NCCL_IB_GID_MATCH_IP` / `WORKER_NCCL_IB_GID_MATCH_IP` 分别指定匹配的 IPv4；未指定时优先取对应 `ETH_IF` 的第一个 IPv4，无法获取时使用节点地址。自动模式忽略 `.env` 中旧的固定 GID，不将识别结果写回文件。

如需手动指定，在 `.env` 中设置 `NCCL_IB_GID_AUTO=0`，并填写 `GID_INDEX` 和 `WORKER_GID_INDEX`；worker 留空时使用 head 值。手动模式也会检查索引在所选活动端口上是否为可读的 RoCE v2。可用 `show_gids`（如已安装）核对索引对应的网卡和 IP。

主节点 API 监听 `0.0.0.0`，使用 host 网络。两台机器的模型路径相同，缓存分别保存在各自宿主机；worker 不启动 API 服务。
保留原包默认推理参数：显存比例 0.80、最大并发 64、单批 8192 tokens、上下文 262144、MTP 3 tokens、`FULL_DECODE_ONLY`。JSON 参数统一由 `.env` 提供，两端共用。

## 启停与验证

通过 1Panel 生命周期入口运行，或在应用目录中执行：

```bash
bash scripts/init.sh
bash scripts/start.sh
bash scripts/stop.sh
bash scripts/restart.sh
bash scripts/uninstall.sh
```

启动前校验模型分片、RoCEv2 设备、GPU 占用和镜像一致性，再同步 worker 补丁；先启动 worker，再启动 head。停止时先停 head，再停 worker。重启会重建两端容器，继续复用宿主机缓存。
同名但不属于本应用的容器会导致操作失败，不会被强制删除。启动失败保留容器和日志供排查，再次启动前执行停止；worker 不可达时停止/卸载会明确报错。

原包报告有缓存时冷启动约 11 分钟，首次 autotune 可能超过 40 分钟，本包默认就绪等待约 50 分钟。worker 健康检查仅确认容器运行，head `/health` 用于判断双机推理引擎就绪。

配套 1Panel 对单次 Qwen 双 GB10 启动、重启脚本限制为 2 小时，覆盖两节点并行拉取镜像（每节点最多 50 分钟）、默认就绪等待 50 分钟及其他部署开销；重启的停止阶段也占用这 2 小时。就绪等待包含检查命令耗时，容器启动时不再拉取镜像。本机拉取直接由 Python 限时；远端拉取限时 50 分钟，主节点额外留出 10 秒等待远端退出和 SSH 收尾。升级保留 1Panel 停服前预拉取或检查本机镜像的原有流程，每镜像拉取超时为 24 小时；随后启动脚本仍按已保存策略处理两节点镜像，远端拉取可能占用停机时间。安装、升级任务外层没有固定总超时。此调整需要同步更新 1Panel 后端，旧版后端仍受 1 小时上限约束。自定义等待时间时需计入镜像拉取等阶段，不能超过面板任务的总时间预算。

```bash
curl -f http://<HEAD_IP>:8000/health
curl http://<HEAD_IP>:8000/v1/chat/completions \
  -H 'Content-Type: application/json' \
  -H 'Authorization: Bearer <API_KEY>' \
  -d '{"model":"Qwen3.8-Flash-Next","messages":[{"role":"user","content":"你好"}],"max_tokens":32}'
```

如修改了端口，请同步修改上述示例。卸载删除两端容器和 worker 部署文件，保留模型权重与编译/autotune 缓存。

## 来源

`mtp_v030_patched.py` 与 `draft_vocab_en_code_47k.txt` 原样来自 `qwen38-dual-share/pkg/qwen38-dual/scripts/patches`；Python 补丁保留其 Apache-2.0 SPDX 声明。集群生命周期沿用现有四机 GB10 应用的实现，保留其 MIT 许可。
