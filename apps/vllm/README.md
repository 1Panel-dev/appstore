## 使用说明

### NVIDIA 版本

1. 在 `https://huggingface.co/` 注册账号并获取模型权限创建 token。
2. 机器上有 NVIDIA GPU。
3. 修改 `/etc/docker/daemon.json` 并增加：

```json
   "runtimes": {
      "nvidia": {
      "path": "nvidia-container-runtime",
      "runtimeArgs": []
      }
   }
```

4. 安装 nvidia-container-runtime 和 nvidia-docker2 组件。

### NVIDIA GB10 双节点版本

使用 1Panel 维护的定制镜像 `1panel/vllm-gb10-dual-node:0.25-optimized-for-ds-v4-flash`。

1. 仅适用于两台固定配置的 NVIDIA GB10 服务器，主节点为 `10.0.1.1`，工作节点为 `10.0.1.2`。
2. 主节点必须能够免密 SSH 到 `10.0.1.2`，两台机器都需要安装 Docker、NVIDIA Container Toolkit，并提供可用的 RoCE/InfiniBand 设备。
3. 两台机器都必须提前准备模型目录 `/opt/1panel/ai/DeepSeek-V4-Flash-0731`。
4. 启动参数、网卡和节点地址保存在应用版本目录的 `.env`，双机启动和停止分别使用 `scripts/start.sh` 与 `scripts/stop.sh`。
5. Qwen 与 DS 均由脚本并行处理两节点镜像，每节点拉取限时 50 分钟，worker 需安装 GNU coreutils（提供 `timeout`），DS 主节点还需 Python 3。启用且提供 VL sidecar 配置时，DS 同时预拉取 sidecar 镜像；每个节点的主镜像与 sidecar 共用这 50 分钟预算。随后启动容器时禁止再次拉取。DS 本机命令由独立监控进程管理，阶段超时或父脚本退出时清理整个命令进程组。远端拉取仍限时 50 分钟，主节点额外留出 10 秒等待远端退出和 SSH 收尾。就绪等待按实际经过时间计时，预算为 `WAIT_ATTEMPTS × WAIT_SECONDS`，默认 200 × 15 秒（50 分钟），包含检查与日志读取耗时。先执行健康检查，未就绪时再读取日志；每个节点单次日志读取最多 5 秒，且不超过剩余就绪预算。1Panel 对单次启动、重启脚本限制为 2 小时，覆盖停止（重启时）、拉取、部署、就绪等待及后续预热。
6. GB10 升级沿用原有流程：1Panel 在停止旧服务前，根据升级表单的拉取选项预拉取或检查本机镜像，每镜像拉取超时为 24 小时。切换后的启动脚本仍按应用已保存的 `IMAGE_PULL_POLICY` 处理各节点镜像，脚本拉取限时 50 分钟；远端镜像未在停服前预拉取。安装和升级任务外层没有固定总超时，2 小时仅约束单次启动、重启脚本。后端和应用包需同步更新。

### NVIDIA GB10 双节点 Qwen3.8 版本

版本 `nvidia-0.30.0-optimized-for-2-gb10-qwen3.8-flash-next-nvfp4` 使用 `vllm/vllm-openai:v0.30.0`，部署 NVIDIA 官方 Qwen3.8-Flash-Next-NVFP4，保留 TP2 + EP + MTP3 配置及补丁。

1. 两端各准备完整权重，默认目录 `/opt/1panel/ai/models/nvidia/Qwen3.8-Flash-Next-NVFP4`；不直接使用旧包的 HF 缓存根目录。
2. 在 `.env` 配置 `CLUSTER_NODES`、`LOCAL_IP` 和两端 RoCE 网卡；主节点 root 需要免密 SSH 到 worker。
3. 表单配置端口、镜像、模型目录和 API Key；默认端口 8000，镜像不可随意升级。
4. 使用 `scripts/init.sh`、`start.sh`、`stop.sh`、`restart.sh`、`uninstall.sh` 管理双机。首次冷启动可能超过 40 分钟，默认就绪等待约 50 分钟。
5. 详细依赖、模型目录结构和缓存路径见版本目录的 `README.md`。

### Ascend 310P 版本

1. 宿主机需要已安装 Ascend 驱动/CANN，执行 `npu-smi info` 能正常看到 NPU。
2. 确认宿主机存在 `/dev/davinci*`、`/dev/davinci_manager`、`/dev/devmm_svm`、`/dev/hisi_hdc` 等设备文件。
3. Ascend 版本的 Compose 已经显式挂载 NPU 设备和驱动目录，不需要开启通用 GPU 配置。
4. Atlas 300I DUO / Ascend 310P 不建议使用 `triton` 或 `triton-ascend`。如果遇到 `module 'triton' has no attribute 'language'`，请检查容器内是否残留相关包并卸载。
5. 推荐优先使用 Qwen3 W8A8SC-310 适配模型；Qwen3.5/Qwen3.6 属于预览支持，需要按模型说明调整启动参数。

## 产品介绍

**vLLM** is a fast and easy-to-use library for LLM inference and serving.

vLLM is fast with:

- State-of-the-art serving throughput
- Efficient management of attention key and value memory with **PagedAttention**
- Continuous batching of incoming requests
- Fast model execution with CUDA/HIP graph
- Quantizations: [GPTQ](https://arxiv.org/abs/2210.17323), [AWQ](https://arxiv.org/abs/2306.00978), INT4, INT8, and FP8.
- Optimized CUDA kernels, including integration with FlashAttention and FlashInfer.
- Speculative decoding
- Chunked prefill

**Performance benchmark**: We include a [performance benchmark](https://buildkite.com/vllm/performance-benchmark/builds/4068) that compares the performance of vLLM against other LLM serving engines ([TensorRT-LLM](https://github.com/NVIDIA/TensorRT-LLM), [text-generation-inference](https://github.com/huggingface/text-generation-inference) and [lmdeploy](https://github.com/InternLM/lmdeploy)).

vLLM is flexible and easy to use with:

- Seamless integration with popular Hugging Face models
- High-throughput serving with various decoding algorithms, including *parallel sampling*, *beam search*, and more
- Tensor parallelism and pipeline parallelism support for distributed inference
- Streaming outputs
- OpenAI-compatible API server
- Support NVIDIA GPUs, AMD CPUs and GPUs, Intel CPUs and GPUs, PowerPC CPUs, TPU, and AWS Neuron.
- Prefix caching support
- Multi-lora support

vLLM seamlessly supports most popular open-source models on HuggingFace, including:

- Transformer-like LLMs (e.g., Llama)
- Mixture-of-Expert LLMs (e.g., Mixtral)
- Embedding Models (e.g. E5-Mistral)
- Multi-modal LLMs (e.g., LLaVA)

Find the full list of supported models [here](https://docs.vllm.ai/en/latest/models/supported_models.html).
