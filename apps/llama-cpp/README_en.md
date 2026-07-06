## Introduction

**llama.cpp** is a lightweight LLM inference project. It runs local GGUF models and provides an OpenAI-compatible API.

## Usage

During installation, set "Model directory" if needed. The default is the app installation directory's `models` directory. Put your GGUF model file there. The default file name is `model.gguf`.

If your model file uses another name, edit the app parameter "Server arguments", for example:

```bash
-m /models/Qwen3-0.6B-Q4_K_M.gguf --host 0.0.0.0 --port 8080
```

After the service starts, open the Web UI in your browser, or use the OpenAI-compatible API:

```bash
http://SERVER_IP:PORT/v1/chat/completions
```

## Features

- Run local GGUF models
- Web UI
- OpenAI-compatible API
- Tune context size, threads, GPU offload, and other options with server arguments
