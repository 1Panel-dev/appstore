## Introduction

**Logstash** is part of the Elastic Stack and works alongside Beats, Elasticsearch, and Kibana. According to the official Elastic GitHub README, Logstash is a server-side data processing pipeline that can ingest data from many sources at the same time, transform it, and send it to your chosen destination.

## Installation Notes

If you want to send the received events to Elasticsearch, edit `pipeline/logstash.conf`, enable the official `elasticsearch` output example included in the file, and replace `hosts` with the actual reachable Elasticsearch address.

## Features

- Ingest events from many different data sources
- Parse, filter, and transform data while it flows through the pipeline
- Send processed data to different destinations through plugins
- Extend input, filter, and output capabilities with a large plugin ecosystem
