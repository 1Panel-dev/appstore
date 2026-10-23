## Introduction

**SGLang** is an open-source inference framework for large language, vision-language, and diffusion models, optimized for agentic workloads, large-scale serving, and RL rollouts.

## Features

- **High-performance inference**: RadixAttention prefix cache reuse improves throughput for multi-turn and long-context workloads.
- **Structured output**: Supports JSON and other structured outputs for agents and tool calling.
- **Multimodal support**: Supports vision-language models (VLMs) and diffusion models.
- **OpenAI-compatible API**: Exposes /v1 compatible endpoints.
- **Tensor parallelism**: Use --tp to scale across multiple GPUs.

## Usage

1. The host requires an NVIDIA GPU, a CUDA 13-compatible driver, and NVIDIA Container Toolkit; enable the "GPU" option during installation. If your driver only supports CUDA 12, use `lmsysorg/sglang:v0.5.19-cu129` (the last CUDA 12 image) as documented upstream.
2. After installation, call the OpenAI-compatible API at `http://<server-ip>:<api-port>/v1` with the API key from the app parameters.
3. Models are downloaded from Hugging Face; enable ModelScope in the parameters for networks in China, or place local models under `./data/models/` in the application installation directory (mounted as `/models` in the container).
4. The first start downloads and loads the model, so the health check may take a while.
5. This package is single-node and does not support RDMA/clustering; use container orchestration or Kubernetes for clusters.
6. `CUSTOM_ARGS` (Custom Arguments) is split on whitespace; quoted arguments are not preserved. For complex arguments, modify the compose file directly.

The default parameter `Qwen/Qwen3.5-4B` is a 4B model suitable for a single GPU; for larger models, adjust `TP_SIZE` to the available GPUs and replace the model.
