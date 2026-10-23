## Introduction

**NATS** is an open-source, high-performance, cloud native messaging system supporting publish/subscribe, request/reply and other messaging patterns.

## Features

- **High Performance**: Lightweight and low-latency, suitable for cloud native, microservice and edge deployments.
- **Multiple Messaging Patterns**: Supports publish/subscribe, request/reply, queue groups and other core messaging patterns.
- **Built-in Monitoring**: Provides an HTTP monitoring endpoint (8222) for connections, subscriptions and runtime status.
- **Multi-language Clients**: Official and community clients are available for more than 40 languages.
- **JetStream Persistence**: JetStream is enabled by default; streams, KV and object store data are persisted under `./data`.

## Deployment

This package is a single-node deployment and does not support clustering. For clustering, use 1Panel container orchestration or Kubernetes.
