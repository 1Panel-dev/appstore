# Ollama

**Ollama** is an open-source large language model service that provides an API interface and chat interface similar to OpenAI. It allows seamless deployment of the latest GPT models and usage through APIs. It supports hot-loading model files, enabling model switching without restarting.

## Quick Start

> Access the `Containers` list, find the Ollama container, and open the terminal to chat with [Llama 2](https://ollama.com/library/llama2):

```
ollama run llama2
```

## Model Library

Ollama supports a variety of models available at [ollama.com/library](https://ollama.com/library).

Below are some example models available for download:

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

> **Note**: You should have at least 8 GB of available RAM to run 7B models, 16 GB for 13B models, and 32 GB for 33B models.