#环境变量备份
```
command: server
container_name: ${CONTAINER_NAME}
environment:
    REDASH_WEB_WORKERS: 2
    PYTHONUNBUFFERED: 0
    REDASH_LOG_LEVEL: INFO
    REDASH_REDIS_URL: ${REDASH_REDIS_URL}
    # REDASH_REDIS_URL: redis://redis:6379/0
    REDASH_COOKIE_SECRET: xvyXYDAYgMNB1hbjJPrha95LkeXLArFP
    REDASH_SECRET_KEY: lfLqjWNNetKNU4gb1uTwPxukNPrunuey
    # POSTGRES_PASSWORD: wxK3u5spuoqI5gaogYdz0yMTkKtZilYy
    REDASH_DATABASE_URL: ${REDASH_DATABASE_URL}
    # REDASH_DATABASE_URL: postgresql://{db-user}:{db-password}@{db-ip}/{db-name}
    # REDASH_DATABASE_URL: postgresql://postgres:wxK3u5spuoqI5gaogYdz0yMTkKtZilYy@postgres/postgres
    REDASH_FEATURE_ALLOW_CUSTOM_JS_VISUALIZATIONS: "true"
    REDASH_ADDITIONAL_QUERY_RUNNERS: "redash.query_runner.python"```