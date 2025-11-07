## Instructions

- The default username is `root`, and the password should be left empty to log in.

## Introduction

**StarRocks** is a next-generation, high-performance analytical data warehouse that supports real-time, multi-dimensional, and high-concurrency data analysis. StarRocks adopts an MPP architecture, is equipped with a fully vectorized execution engine, supports a real-time updatable columnar storage engine, and boasts a rich set of features, including a fully customized Cost-Based Optimizer (CBO), intelligent materialized views, and more. StarRocks supports real-time and batch data ingestion from various data sources and can directly analyze data stored in data lakes without requiring data migration.

**StarRocks** is also MySQL-compatible and can be easily connected to using MySQL clients and common BI tools. It is highly scalable, highly available, and easy to maintain. StarRocks is widely used in the industry to support various OLAP application scenarios, such as real-time analytics, ad-hoc queries, and data lake analytics.

## Scenarios

StarRocks meets varied enterprise analytics requirements, including OLAP multi-dimensional analytics, real-time analytics, high-concurrency analytics, customized reporting, ad-hoc queries, and unified analytics.

### OLAP multi-dimensional analytics

The MPP framework and vectorized execution engine enable users to choose between various schemas to develop multi-dimensional analytical reports.

Scenarios:

- User behavior analysis
- User profiling, label analysis, user tagging
- High-dimensional metrics report
- Self-service dashboard
- Service anomaly probing and analysis
- Cross-theme analysis
- Financial data analysis
- System monitoring analysis

### Real-time analytics

StarRocks uses the Primary Key table to implement real-time updates. Data changes in a TP database can be synchronized to StarRocks in a matter of seconds to build a real-time warehouse.

Scenarios:

- Online promotion analysis
- Logistics tracking and analysis
- Performance analysis and metrics computation for the financial industry
- Quality analysis for livestreaming
- Ad placement analysis
- Cockpit management
- Application Performance Management (APM)

### High-concurrency analytics

StarRocks leverages performant data distribution, flexible indexing, and intelligent materialized views to facilitate user-facing analytics at high concurrency:

- Advertiser report analysis
- Channel analysis for the retail industry
- User-facing analysis for SaaS
- Multi-tabbed dashboard analysis

### Unified analytics

StarRocks provides a unified data analytics experience.

- One system can power various analytical scenarios, reducing system complexity and lowering TCO.
- StarRocks unifies data lakes and data warehouses. Data in a lakehouse can be managed all in StarRocks. Latency-sensitive queries that require high concurrency can run on StarRocks. Data in data lakes can be accessed by using external catalogs or external tables provided by StarRocks.
