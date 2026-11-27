## Before installation

This app uses the official Apache image for new installations. If you already run the **Kafka (Bitnami)** app, keep that instance and choose an unused TCP port for the new one. Use the new instance's own data directory. When migrating, verify topics, messages, and consumer group offsets before retiring the old instance.

## Introduction

**Kafka (Apache)** stores events in topics. Producers write records, and consumers read them in partition order and resume from their committed offsets. This app runs the KRaft controller and broker in one container and stores records and cluster metadata in the installation directory under `data/`.

## Connect a client

- **On the server**, use `127.0.0.1:9092` by default. If you changed the TCP port during installation, use that port.
- **From another host**, set **Client access host** to the server IP address or domain that clients can reach. Enter the host alone and enable external access to the port in 1Panel. Connect using that host and the configured TCP port. This listener uses plaintext, so limit access to trusted networks.
- **From a container on the same `1panel-network`**, use `<Kafka-container-name>:19092`.
