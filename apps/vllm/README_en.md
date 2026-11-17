## Setup Notes

### NVIDIA versions

1. Register an account at `https://huggingface.co/` and get model access to create a token.
2. Ensure the machine has an NVIDIA GPU.
3. Modify the `/etc/docker/daemon.json` file and add:

```json
   "runtimes": {
      "nvidia": {
      "path": "nvidia-container-runtime",
      "runtimeArgs": []
      }
   }
```

4. Install the nvidia-container-runtime and nvidia-docker2 components.

### NVIDIA GB10 Dual Node Version

Uses the custom image `1panel/vllm-gb10-dual-node:0.25-optimized-for-ds-v4-flash` maintained by 1Panel.

1. This version targets two fixed NVIDIA GB10 servers: head `10.0.1.1` and worker `10.0.1.2`.
2. The head must have passwordless SSH access to `10.0.1.2`. Both hosts require Docker, NVIDIA Container Toolkit, and a working RoCE/InfiniBand device.
3. The model must already exist at `/opt/1panel/ai/DeepSeek-V4-Flash-0731` on both hosts.
4. Runtime parameters, interfaces, and node addresses are stored in the version `.env`; use `scripts/start.sh` and `scripts/stop.sh` for the two-node lifecycle.

### NVIDIA GB10 Dual Node Qwen3.8 Version

Version `nvidia-0.30.0-optimized-for-2-gb10-qwen3.8-flash-next-nvfp4` uses `vllm/vllm-openai:v0.30.0` with the official NVIDIA Qwen3.8-Flash-Next-NVFP4 weights, preserving TP2 + EP + MTP3 and the bundled patches.

1. Prepare complete weights on both hosts at `/opt/1panel/ai/models/nvidia/Qwen3.8-Flash-Next-NVFP4`; the model directory is not the legacy HF cache root.
2. Configure `CLUSTER_NODES`, `LOCAL_IP`, and both RoCE interfaces in `.env`. The head root account needs passwordless SSH access to the worker.
3. The form exposes the port, image, model directory, and API key. The default port is 8000; do not upgrade the patched vLLM image without validation.
4. Use `scripts/init.sh`, `start.sh`, `stop.sh`, `restart.sh`, and `uninstall.sh` for the two-node lifecycle. Initial autotuning can exceed 40 minutes; readiness waits for approximately 50 minutes by default.
5. See the version README for prerequisites, model layout, and persistent cache paths.

### Ascend 310P version

1. The host must have the Ascend driver/CANN installed, and `npu-smi info` must list the NPU correctly.
2. Verify that `/dev/davinci*`, `/dev/davinci_manager`, `/dev/devmm_svm`, and `/dev/hisi_hdc` exist on the host.
3. The Ascend Compose file mounts NPU devices and driver directories explicitly, so do not enable the generic GPU configuration.
4. Atlas 300I DUO / Ascend 310P should not depend on `triton` or `triton-ascend`. If `module 'triton' has no attribute 'language'` appears, check and uninstall residual packages in the container.
5. Prefer Qwen3 W8A8SC-310 adapted models for stable use. Qwen3.5/Qwen3.6 support is preview-level and may need model-specific launch arguments.

## Introduction

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
