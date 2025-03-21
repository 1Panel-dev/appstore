# NodeBB
**NodeBB** is a forum software powered by Node.js, supporting Redis, MongoDB, or PostgreSQL databases. It leverages WebSocket for real-time interaction and notifications. NodeBB incorporates modern web features such as real-time streaming discussions, mobile responsiveness, a rich RESTful read/write API, while staying true to the traditional bulletin board/forum format → categorized hierarchy, local user accounts, and asynchronous messaging.

NodeBB itself includes a basic "universal core" with essential functionality, while additional features and integrations are implemented through third-party plugins.

> After the container starts for the first time, wait for the npm modules to install. Once the logs display `Web installer listening on http://0.0.0.0:4567`, you can proceed with the web installer for configuration.

## Databases
NodeBB has a database abstraction layer (DBAL) that allows users to write drivers for their preferred database. Currently, the following options are available:

### MongoDB (Default)
If you are concerned about running out of memory with Redis or want your forum to scale more easily, you can install NodeBB with MongoDB. This tutorial assumes you know how to SSH into your server and have root access.

These instructions are for Ubuntu. Adjust accordingly for your distribution.

Note: If you need to prepend `sudo` to any commands, please do so. Nobody will hold it against you ;)

[Configure MongoDB](https://docs.nodebb.org/configuring/databases/mongo/#step-1-install-mongodb) (For Docker, refer to steps 6 and 7).

### Redis
NodeBB uses MongoDB as its primary data store by default, although Redis is also supported. In NodeBB, both Redis and MongoDB are treated as first-class data stores. If you encounter errors using Redis instead of MongoDB, we still encourage you to [submit an issue](https://github.com/NodeBB/NodeBB/issues/new).

Redis is designed to keep the entire database in active memory, which can be very useful in certain high-scalability scenarios. Compared to MongoDB, Redis may offer some marginal speed improvements.

However, this advantage comes with a trade-off. Since Redis keeps the entire database in active memory, the system memory needs to be at least twice the size of the dataset (e.g., 2GB of data requires at least 4GB of system memory). Redis requires twice the memory of the dataset because its backup strategy involves cloning the in-memory data before persisting it to disk.

Carefully consider your hardware options to see if the trade-offs are worth it for your installation. Remember, the NodeBB team offers migration services from Redis to MongoDB for users who need it. For more information, contact sales@nodebb.org.

[Configure Redis](https://docs.nodebb.org/configuring/databases/redis/#step-1-install-redis) (For Docker, refer to step 2).

### PostgreSQL
Note: While NodeBB fully supports PostgreSQL, we still recommend using MongoDB and Redis, as these are what we use for our own and hosted clients.

PostgreSQL requires no additional configuration.