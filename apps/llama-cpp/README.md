## 产品介绍

**llama.cpp** 是一个轻量级的大语言模型推理项目，支持在本地使用 GGUF 模型运行推理服务，并提供兼容 OpenAI 的接口。

## 使用说明

安装时可填写“模型目录”，默认是应用安装目录下的 `models` 目录。将 GGUF 模型文件放到该目录中，默认文件名为 `model.gguf`。

如果模型文件名不同，请在应用参数中修改“服务启动参数”，例如：

```bash
-m /models/Qwen3-0.6B-Q4_K_M.gguf --host 0.0.0.0 --port 8080
```

服务启动后，可通过浏览器访问 Web UI，也可以使用 OpenAI 兼容接口：

```bash
http://服务器 IP:端口/v1/chat/completions
```

## 主要功能

- 本地运行 GGUF 模型
- 提供 Web UI
- 提供 OpenAI 兼容 API
- 支持通过启动参数调整上下文长度、线程数、GPU offload 等选项
