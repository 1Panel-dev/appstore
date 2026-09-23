## Introduction

**WebStats** is a self-hosted website analytics platform. The server side is plain PHP + MySQL + Redis (no Composer, no phpredis extension required), the dashboard is built with React + Semi Design, and the tracking SDK has zero dependencies. Every byte of your traffic data stays on your own server.

## Features

- **Core metrics**: pageviews, unique visitors, unique IPs, sessions, average duration and bounce rate, with realtime online count and today's hourly curve
- **Traffic sources**: channel breakdown and trend, referrers, UTM parameters, ad platforms, search engine keywords
- **Session drill-down**: session list with the full event log of each visit, page drill-down, visitor journey, funnels and conversion goals
- **Geo and clients**: IPv4 / IPv6 offline IP databases, browser / OS / device / screen analysis
- **Multi-site and multi-user**: two-step file verification per site, owner / editor / viewer roles, complete operation log
- **Data access**: open API with PAT tokens, CSV export, traffic anomaly alerts and daily reports

## Usage

- Default port is `8080` and can be changed during installation
- Sign in with the **admin email** and **admin password** you set during installation
- MySQL and Redis are reused from the 1Panel app store
- To track a website: Sites → add site → file verification → copy the SDK snippet into the site's `<head>`
- Behind a reverse proxy or CDN, switch **Visitor IP Source** to `X-Forwarded-For` or `X-Real-IP`, otherwise all visitor IPs will be the proxy address
