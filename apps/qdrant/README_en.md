## Open the dashboard

**Default URL** `http://SERVER_IP:6333/dashboard`. Replace `SERVER_IP` with your server's IP address. If you changed the HTTP port during installation, replace `6333` as well.

In 1Panel, find Qdrant under **App Store → Installed** and click **Parameters**. Under **Web Access Address**, click **Custom**, select `http`, enter `SERVER_IP:6333/dashboard`, and click **Confirm**. You can then use **Open** in the installed apps list to reach the dashboard.

## Introduction

**Qdrant** is an open-source vector database for finding similar documents, products or images by their embeddings. It can power semantic search, recommendations and retrieval-augmented generation (RAG).

## Features

- **Hybrid search** combines dense and sparse vectors in one query to match both meaning and keywords.
- **Metadata filters** narrow results using fields in the JSON payload. For example, a search for similar products can return only items in stock within a given price range.
- Use the **built-in dashboard** with the API key from installation to manage collections, or connect an application over REST or gRPC.
- **Collection snapshots** back up vectors, indexes and payloads for individual collections.
