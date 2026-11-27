## 产品介绍

**Xinference** 是开源模型推理平台，可在 Web 界面部署和管理大语言模型、嵌入模型及其他 AI 模型，并通过兼容 OpenAI 的 API 调用。

此安装包适用于带 NVIDIA GPU 的 AMD64 Linux 主机。按官方要求，宿主机须安装支持 CUDA 13.0 的 NVIDIA 驱动（580 或更新）和 NVIDIA Container Toolkit。安装时请在高级设置中勾选「开启 GPU 支持」。[镜像运行要求](https://inference.readthedocs.io/en/stable/getting_started/using_docker_image.html)

首次访问 Web 界面时，从容器启动日志取得初始化令牌并创建管理员。账号配置、模型文件和下载缓存会保留在应用数据目录中。[首次认证说明](https://inference.readthedocs.io/en/stable/user_guide/auth_system.html)
