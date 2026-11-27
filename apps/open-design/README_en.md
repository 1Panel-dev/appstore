## Introduction

**Open Design** is an open-source AI design tool for creating prototypes, presentations, and other design assets from natural-language prompts. You can preview and edit the results in the workspace.

Generate an API token with `openssl rand -hex 32` and enter it in the installation form. If the browser prompts you to sign in, use `open-design` as the username and the same token as the password.

The app listens on localhost by default. For remote access, use an authenticated HTTPS reverse proxy or an SSH tunnel. When using a proxy with a domain, add the browser's full origin to **Allowed Origins**, such as `https://design.example.com`. Separate multiple origins with commas. [Official deployment guide](https://github.com/nexu-io/open-design/blob/open-design-v0.27.1/deploy/README.md)

To generate designs, you can connect a model provider in the web interface. To use a local agent, follow the upstream instructions to make its CLI available to the container. [Agent CLI setup](https://github.com/nexu-io/open-design/blob/open-design-v0.27.1/deploy/README.md#linux-mounting-host-agent-clis)
