## Introduction

**Dragonfly** is an in-memory database compatible with the Redis and Memcached protocols. It can serve application caches, store sessions and maintain live leaderboards. This package enables the Redis protocol by default, so you can connect with `redis-cli` or an existing Redis client.

## Features

- **Multicore processing** lets one instance handle reads and writes in parallel across CPU cores.
- Supported **data structures** include strings, hashes, lists, sets and sorted sets. For example, a hash can hold user attributes, while a sorted set can rank players by score.
- Set an expiration time on individual keys, or enable **cache mode** to evict entries as memory approaches its limit.
- Dragonfly saves a **snapshot** to the data directory on graceful shutdown and loads it on startup. You can also take a background snapshot with `BGSAVE` or configure a snapshot schedule.
