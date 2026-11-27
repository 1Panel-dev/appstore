## Introduction

**Xinference** is an open-source inference platform. It provides a web interface for deploying and managing large language, embedding, and other AI models, and exposes an OpenAI-compatible API.

This package runs on AMD64 Linux hosts with NVIDIA GPUs. The host needs a CUDA 13.0-compatible NVIDIA driver (version 580 or later) and NVIDIA Container Toolkit. Select **Enable GPU support** in the advanced settings when installing. [Image requirements](https://inference.readthedocs.io/en/stable/getting_started/using_docker_image.html)

On first access, find the setup token in the container startup logs and use it to create the administrator account in the web interface. Account settings, model files, and download caches are kept in the app data directory. [Authentication guide](https://inference.readthedocs.io/en/stable/user_guide/auth_system.html)
