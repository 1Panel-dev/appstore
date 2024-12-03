# Open WebUI

**Open WebUI** is a user-friendly WebUI for LLM users, supporting LLM runtime programs such as Ollama and OpenAI compatible APIs.

## Main Features:

- **Intuitive Interface**: Our chat interface is inspired by ChatGPT, ensuring a user-friendly experience.
- **Responsive Design**: Enjoy a seamless experience on both desktop and mobile devices.
- **Fast Response**: Experience fast response performance.
- **Easy Deployment**: Seamlessly install using Docker or Kubernetes (kubectl, kustomize, or helm) for a hassle-free experience.
- **Code Syntax Highlighting**: Enhance code readability with our syntax highlighting feature.
- **Markdown and LaTeX Support**: Enrich interactions with comprehensive Markdown and LaTeX features to enhance your LLM experience.
- **RAG Integration**: Support for cutting-edge retrieval-augmented generation (RAG) that allows you to deeply explore the future of chat interactions. This feature seamlessly integrates document interactions into your chat experience. You can load documents directly into the chat or add files to the document library and easily access them using #prompt commands. During the alpha phase, we are actively refining and enhancing this feature to ensure optimal performance and reliability, though occasional issues may arise.
- **Web Browsing Capabilities**: Seamlessly integrate websites into your chat experience using commands with `#URL`. This feature lets you directly merge web content into your conversation, enriching the depth and interaction.
- **Prompt Presets Support**: Instantly access preset prompts in the chat input. Easily load predefined conversation starters and speed up your interactions. You can easily import prompts via Open WebUI Community integration.
- **RLHF Annotation**: Enhance the capability of your messages by rating them "upvote" and "downvote" to facilitate the creation of reinforcement learning datasets based on human feedback (RLHF). Use your messages to train or fine-tune models while ensuring local data confidentiality.
- **Conversation Management**: Easily categorize and locate specific chats for quick reference and simplified data collection.
- **Download/Delete Models**: Easily download or delete models directly from the Web UI.
- **GGUF File Model Creation**: Easily create Ollama models by uploading GGUF files directly from the Web UI. A simplified process allows you to upload from your computer or download GGUF files from Hugging Face.
- **Multi-Model Support**: Seamlessly switch between different chat models for diverse interactions.
- **Multi-Mode Support**: Interact seamlessly with models that support multi-modal interactions, including images (e.g., LLava).
- **Model File Generator**: Easily create Ollama model files through the Web UI. Open WebUI Community integration allows you to create and add roles/agents, customize chat elements, and import model files with ease.
- **Multiple Model Conversations**: Interact simultaneously with multiple models, leveraging their unique strengths to get the best responses. Parallelly utilizing a range of different models enhances your experience.
- **Collaborative Chat**: Seamlessly orchestrate group conversations to harness the collective intelligence of multiple models. Use the @command to specify models and enable dynamic and diverse conversations in the chat interface. Immerse yourself in the collective wisdom within the chat environment.
- **Regeneration History Access**: Easily revisit and explore your entire regeneration history.
- **Chat History Management**: Easily access and manage your chat history.
- **Import/Export Chat History**: Seamlessly import and export your chat data into and out of the platform.
- **Voice Input Support**: Interact with your model through voice; enjoy the convenience of speaking directly with the model. Additionally, explore the option to automatically send voice input after 3 seconds of silence for a streamlined experience.
- **Advanced Parameter Fine-Tuning Control**: Gain deeper control over your conversations by adjusting parameters like temperature and defining system prompts, allowing you to tailor your interaction based on your specific preferences and needs.
- **Image Generation Integration**: Seamlessly integrate image generation features with AUTOMATIC1111 API (local) and DALL-E, enriching your chat experience with dynamic visual content.
- **OpenAI API Integration**: Easily integrate OpenAI compatible APIs for multi-functional conversations with Ollama models. Customize the API base URL to link with LMStudio, Mistral, OpenRouter, etc.
- **Multiple OpenAI Compatible API Support**: Seamlessly integrate and customize various OpenAI compatible APIs to enhance the multi-functionality of your chat interactions.
- **External Ollama Server Connection**: Seamlessly connect to external Ollama servers hosted at different addresses by configuring environment variables.
- **Multiple Ollama Instance Load Balancing**: Effortlessly distribute chat requests across multiple Ollama instances to enhance performance and reliability.
- **Multi-User Management**: Easily monitor and manage users through our intuitive management panel, simplifying the user management process.
- **Role-Based Access Control (RBAC)**: Ensure secure access with restricted permissions; only authorized individuals can access your Ollama, with exclusive model creation/pulling rights reserved for administrators.
- **Backend Reverse Proxy Support**: Enhance security with direct communication between the Open WebUI backend and Ollama. This key feature eliminates the need to expose Ollama over LAN. Requests routed from the Web UI to `/ollama/api` will seamlessly redirect to Ollama from the backend, improving overall system security.
- **Continuous Updates**: We are committed to improving Open WebUI with regular updates and new features.

**Open WebUI** offers a broad feature set and high scalability, making it the ideal tool for LLM users.