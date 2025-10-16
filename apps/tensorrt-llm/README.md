## 使用说明

1. 在 `https://huggingface.co/` 注册账号并下载模型文件到服务器目录
2. 机器上有 NVIDIA GPU
3. 修改 `/etc/docker/daemon.json` 并增加

```json
   "runtimes": {
      "nvidia": {
      "path": "nvidia-container-runtime",
      "runtimeArgs": []
      }
   }
```

4. 安装 nvidia-container-runtime 和 nvidia-docker2 组件
5. 以 `Qwen3-32B-FP4` 模型为例，如果模型存放目录为 `/opt/Qwen3-32B-FP4`，则创建应用时的模型目录参数填写 `/opt`，模型名称参数填写 `Qwen3-32B-FP4`

## 产品介绍

TensorRT LLM is an open-sourced library for optimizing Large Language Model (LLM) inference. It provides state-of-the-art optimizations, including custom attention kernels, inflight batching, paged KV caching, quantization (FP8, FP4, INT4 AWQ, INT8 SmoothQuant, ...), speculative decoding, and much more, to perform inference efficiently on NVIDIA GPUs.

Architected on PyTorch, TensorRT LLM provides a high-level Python LLM API that supports a wide range of inference setups - from single-GPU to multi-GPU or multi-node deployments. It includes built-in support for various parallelism strategies and advanced features. The LLM API integrates seamlessly with the broader inference ecosystem, including NVIDIA Dynamo and the Triton Inference Server.

TensorRT LLM is designed to be modular and easy to modify. Its PyTorch-native architecture allows developers to experiment with the runtime or extend functionality. Several popular models are also pre-defined and can be customized using native PyTorch code, making it easy to adapt the system to specific needs.
