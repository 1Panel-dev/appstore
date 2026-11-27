## Introduction

**SearXNG** is an open-source metasearch engine that brings results from multiple search services into one page. You can choose which sources your instance queries.

On first startup, the app creates `config/settings.yml` with default settings and a random secret. The favicon cache is stored in the `data` directory.

To use the JSON search API, add `json` alongside `html` under `search.formats` in `config/settings.yml`, then restart the app. [Search API documentation](https://docs.searxng.org/dev/search_api.html)
