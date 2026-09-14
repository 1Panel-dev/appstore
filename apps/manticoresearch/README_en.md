## Introduction

**Manticore Search** is a high-performance, multi-storage database designed for search and analytics. It delivers ultra-fast full-text search, real-time indexing, and advanced capabilities such as vector search and columnar storage, enabling efficient data analysis. Manticore Search scales seamlessly from small datasets to massive data volumes, providing powerful insights for modern applications.

> **Warning**: Manticore Search does **not** provide built-in user authentication. **Do not expose it to the public internet** unless it is placed behind a reverse proxy with proper authentication enabled.

## Possible Use Cases

Manticore Search is highly versatile and can be applied to a wide range of scenarios, including:

- **Full-Text Search**:
  - Ideal for e-commerce platforms, enabling fast and accurate product search with features such as autocomplete and fuzzy matching.
  - Well suited for content-heavy websites, allowing users to quickly find relevant articles or documents.

- **Data Analytics**:
  - Ingest data into Manticore Search using [Beats/Logstash](https://manticoresearch.com/blog/integration-of-manticore-with-logstash-filebeat/), [Vector.dev](https://manticoresearch.com/blog/integration-of-manticore-with-vectordev/), or [Fluent Bit](https://manticoresearch.com/blog/integration-of-manticore-with-fluentbit/).
  - Efficiently analyze large datasets using Manticore’s columnar storage and OLAP capabilities.
  - Execute complex queries on terabytes of data with extremely low latency.
  - Visualize data using tools such as Kibana, [Grafana](https://manticoresearch.com/blog/manticoresearch-grafana-integration/), or [Apache Superset](https://manticoresearch.com/blog/manticoresearch-apache-superset-integration/).

- **Faceted Search**:
  - Allow users to filter search results by categories such as price, brand, or date for a more refined search experience.

- **Geospatial Search**:
  - Leverage Manticore’s geospatial features to perform location-based searches, such as finding nearby restaurants or stores.

- **Spell Correction**:
  - Automatically correct spelling mistakes in user queries to improve search accuracy and overall user experience.

- **Autocomplete**:
  - Provide real-time suggestions as users type, improving usability and search speed.

- **Data Stream Filtering**:
  - Use percolate tables to efficiently filter and process real-time data streams, such as social media feeds or log data.