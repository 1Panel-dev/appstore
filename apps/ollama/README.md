# ollama

**Ollama** 是一个开源的大型语言模型服务，提供了类似 OpenAI 的 API 接口和聊天界面，可以非常方便地部署最新版本的 GPT 模型并通过接口使用。支持热加载模型文件，无需重新启动即可切换不同的模型。

## 快速开始

> 进入 `容器` 列表，找到 Ollama 容器，点击进入终端运行并与 [Llama 2](https://ollama.com/library/llama2) 聊天：

```
ollama run llama2
```

## 模型库

Ollama 支持 [ollama.com/library](https://ollama.com/library) 上提供的一系列模型

以下是一些可以下载的示例模型：

| Model              | Parameters | Size  | Download                       |
| ------------------ | ---------- | ----- | ------------------------------ |
| Llama 2            | 7B         | 3.8GB | `ollama run llama2`            |
| Mistral            | 7B         | 4.1GB | `ollama run mistral`           |
| Dolphin Phi        | 2.7B       | 1.6GB | `ollama run dolphin-phi`       |
| Phi-2              | 2.7B       | 1.7GB | `ollama run phi`               |
| Neural Chat        | 7B         | 4.1GB | `ollama run neural-chat`       |
| Starling           | 7B         | 4.1GB | `ollama run starling-lm`       |
| Code Llama         | 7B         | 3.8GB | `ollama run codellama`         |
| Llama 2 Uncensored | 7B         | 3.8GB | `ollama run llama2-uncensored` |
| Llama 2 13B        | 13B        | 7.3GB | `ollama run llama2:13b`        |
| Llama 2 70B        | 70B        | 39GB  | `ollama run llama2:70b`        |
| Orca Mini          | 3B         | 1.9GB | `ollama run orca-mini`         |
| Vicuna             | 7B         | 3.8GB | `ollama run vicuna`            |
| LLaVA              | 7B         | 4.5GB | `ollama run llava`             |
| Gemma              | 2B         | 1.4GB | `ollama run gemma:2b`          |
| Gemma              | 7B         | 4.8GB | `ollama run gemma:7b`          |

> 注意：您应该至少有 8 GB 可用 RAM 来运行 7B 型号，16 GB 来运行 13B 型号，32 GB 来运行 33B 型号。
