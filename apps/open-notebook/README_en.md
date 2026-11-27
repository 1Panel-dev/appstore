## Introduction

**Open Notebook** is an open-source AI research notebook. It organizes documents, websites, audio, and video in notebooks and uses your chosen models to answer questions about sources, summarize them, and generate podcasts.

Set separate strong values for the access password, database password, and encryption key at installation. Use `openssl rand -hex 32` to generate the encryption key. Keep that key for upgrades and restores so existing model provider credentials remain readable.

Direct access uses API port `5055` by default. If you change that port, set Base URL to the API address your browser can reach. For HTTPS reverse proxy access, you can proxy the Web port alone and set Base URL to the public HTTPS origin, with no `/api` suffix. [Reverse proxy guide](https://github.com/lfnovo/open-notebook/blob/main/docs/5-CONFIGURATION/reverse-proxy.md)
