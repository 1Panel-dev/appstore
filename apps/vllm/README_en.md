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

### Ascend 310P version

1. The host must have the Ascend driver/CANN installed, and `npu-smi info` must list the NPU correctly.
2. Verify that `/dev/davinci*`, `/dev/davinci_manager`, `/dev/devmm_svm`, and `/dev/hisi_hdc` exist on the host.
3. The Ascend Compose file mounts NPU devices and driver directories explicitly, so do not enable the generic GPU configuration.
4. Atlas 300I DUO / Ascend 310P should not depend on `triton` or `triton-ascend`. If `module 'triton' has no attribute 'language'` appears, check and uninstall residual packages in the container.
5. Prefer Qwen3 W8A8SC-310 adapted models for stable use. Qwen3.5/Qwen3.6 support is preview-level and may need model-specific launch arguments.

### Ascend 910B (openEuler) version

1. Targets Atlas 800 / Atlas 300T Pro and other 910B-series NPU servers. Image: `vllm-ascend:v0.20.2rc1-openeuler`.
2. The host must have the Ascend driver/CANN installed, and `npu-smi info` must correctly identify the 910B devices.
3. The Compose file uses `ipc: host`, `privileged: true`, `seccomp=unconfined`, and explicitly mounts `/dev/davinci0`–`/dev/davinci7` plus management devices.
4. The default launch template is the Qwen3.6-35B-A3B dual-card (`ASCEND_RT_VISIBLE_DEVICES=0,1`) best-practice command, including tensor-parallel-size 2, expert-parallel, qwen3_5_mtp speculative decoding, FULL_DECODE_ONLY cudagraph, and enable_cpu_binding.
5. `MODEL_DIR` defaults to `/data01/models`, `MODEL_NAME` defaults to `Qwen3.6-35B-A3B`; the in-container working directory is `/workspace`.
6. `HCCL_BUFFSIZE` is set to 1024 (recommended for 910B); lower it if you encounter memory pressure.

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
