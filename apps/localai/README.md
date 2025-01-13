# LocalAI

**LocalAI** 是免费的开源 OpenAI 替代品。LocalAI 可作为替代 REST API，与 OpenAI（Elevenlabs、Anthropic……）API 规范兼容，用于本地 AI 推理。它允许您在本地或使用消费级硬件运行 LLM、生成图像、音频（不止于此），支持多种模型系列。不需要 GPU。

## 主要功能：

- 使用 GPT 生成文本（llama.cpp、transformers、vllm 等等）
- 文本转音频
- 音频转文本（带音频转录 whisper.cpp）
- 图像生成
- 类似 OpenAI 的工具 API
- 向量数据库的嵌入生成
- 受限语法
- 直接从 Huggingface 下载模型
- 视觉 API
- 重新排序 API
- P2P 推理
- 语音活动检测（Silero-VAD 支持）
- 集成 WebUI！