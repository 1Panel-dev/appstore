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
