## Introduction

**ZeroClaw** is a self-hosted personal AI assistant written in Rust. Use the web dashboard to chat with your assistant, manage memory and scheduled tasks, or connect messaging channels.

## First use

1. Open the application logs and find the pairing code printed on first startup.
2. Open the ZeroClaw dashboard and enter the code. Follow the Quickstart wizard to select a model provider, enter an API key or local model endpoint, and create your first agent.

To pair another browser, run this command on the server. Replace `CONTAINER_NAME` with the ZeroClaw container name shown in 1Panel.

```sh
docker exec CONTAINER_NAME zeroclaw gateway get-paircode --new
```
