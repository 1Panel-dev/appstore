## Usage

After installation, visit `http://server-address:port/index.php?a=install` to initialize the site.

The application uses SQLite by default and does not require a database service. Create a reverse proxy for the application port in 1Panel to use a domain and HTTPS.

## Persistent Data

- `data`: site configuration, SQLite database, and runtime data
- `avatars`: user avatars
- `upload`: uploaded attachments
- `plugins`: installed plugins

The separate `cron` container runs scheduled jobs every minute.

## Introduction

**bbs1org** is a minimalist native PHP forum supporting SQLite, MySQL, and PostgreSQL. It includes forums, topics, replies, user groups, permissions, attachments, and plugins.
