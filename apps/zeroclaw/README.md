## 产品介绍

**ZeroClaw** 是一款使用 Rust 编写的自托管个人 AI 助手。你可以在 Web 控制台中与助手对话、管理记忆和定时任务，也可以连接消息渠道。

## 首次使用

1. 打开应用日志，找到首次启动时输出的配对码。
2. 打开 ZeroClaw 的 Web 控制台并输入配对码。首次配对后，按快速设置向导选择模型供应商、填写 API 密钥或本地模型地址，并创建助手。

如需为其他浏览器配对，请在服务器终端运行以下命令，将 `CONTAINER_NAME` 替换为 1Panel 中显示的 ZeroClaw 容器名称。

```sh
docker exec CONTAINER_NAME zeroclaw gateway get-paircode --new
```
