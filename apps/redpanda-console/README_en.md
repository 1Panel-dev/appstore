# Redpanda Console

**Redpanda Console** is a developer-friendly UI for managing Kafka/Redpanda workloads. The console provides an interactive way to understand topics, mask data, manage consumer groups, and explore real-time data through time-travel debugging.

## Main Features:

- **Message Viewer**: Explore your topic messages through ad-hoc queries and dynamic filters in our message viewer. Use JavaScript functions to filter messages and find exactly what you’re looking for. Supported encodings include JSON, Avro, Protobuf, XML, MessagePack, Text, and Binary (hex view). Encodings (except Protobuf) are automatically recognized.
- **Consumer Groups**: List all active consumer groups and their group offset, edit group offsets (by group, topic, or partition), or delete consumer groups.
- **Topic Overview**: Browse Kafka topic lists, inspect their configurations, space usage, list all consumers using a single topic, or observe partition details (e.g., low watermark, high watermark, message count, etc.), embed topic documentation from git repositories, and more.
- **Cluster Overview**: List ACLs, available brokers, their space usage, rack IDs, and other information for a high-level overview of brokers in the cluster.
- **Schema Registry**: List all Avro, Protobuf, or JSON schemas in the schema registry.
- **Kafka Connect**: Manage connectors from multiple connect clusters, patch configurations, view their current status, or restart tasks.

