## Introduction

**Consul** is a distributed, highly available, and data center-aware solution for connecting and configuring applications across dynamic, distributed infrastructures.

## Features

- **Multi-Data Center**: Consul is designed to be data center-aware and can support any number of regions without complex configuration.
- **Service Mesh**: Consul's service mesh enables secure service-to-service communication with automatic TLS encryption and identity-based authorization. Applications can use sidecar proxies in the service mesh configuration to establish TLS connections for inbound and outbound traffic via transparent proxies.
- **API Gateway**: The Consul API Gateway manages access to services within the Consul service mesh, allowing users to define traffic and authorization policies for services deployed in the mesh.
- **Service Discovery**: Consul makes it easy for services to register themselves and discover other services via DNS or HTTP interfaces. External services, such as SaaS providers, can also be registered.
- **Health Checks**: Health checks allow Consul to quickly alert operators to any issues in the cluster. Integration with service discovery prevents routing traffic to unhealthy hosts and enables service-level circuit breakers.
- **Dynamic Application Configuration**: An HTTP API allows users to store indexed objects in Consul for storing configuration parameters and application metadata.
