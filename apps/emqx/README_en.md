## Introduction

**EMQX** is an open-source MQTT message broker built on Erlang/OTP, specifically designed to support the MQTT protocol. It offers a range of powerful features, making it an ideal choice for IoT (Internet of Things) applications. Below are the main features of EMQX:

## Features

- **MQTT 3.1 and 3.1.1 Support**: EMQX supports versions 3.1 and 3.1.1 of the MQTT protocol, enabling devices and applications to communicate efficiently using MQTT.
- **MQTT 5.0 Support**: EMQX also supports the MQTT 5.0 protocol, which introduces additional features such as properties, shared subscriptions, session expiration, and more, enhancing message delivery flexibility.
- **Clustering Support**: EMQX has robust clustering capabilities, allowing horizontal scaling to handle large-scale message traffic and connection requests. This ensures high availability and scalability.
- **Plugin System**: EMQX provides a rich plugin system, enabling users to extend its functionality as needed, including plugins for authentication, authorization, data storage, message forwarding, and more.
- **Security**: EMQX offers multi-layered security, including username and password-based authentication, TLS/SSL support for encrypted communication, ACL (Access Control Lists), and MQTT security features.
- **Last Will Messages**: EMQX supports MQTT last will messages, ensuring messages are appropriately handled after a device disconnects.
- **Data Storage**: EMQX supports various data storage backends, including Mnesia, MySQL, PostgreSQL, and Redis, for flexible message data storage.
- **Visualization and Monitoring**: EMQX provides a user-friendly web interface for monitoring key metrics such as connections, sessions, message publishing, and subscriptions.
- **Load Balancing**: EMQX features load balancing capabilities, automatically distributing connections and messages evenly across cluster nodes to improve performance and stability.
- **High Availability**: EMQX supports hot backups and failover, ensuring high availability even in the event of node failures.
