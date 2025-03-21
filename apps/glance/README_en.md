# Glance
**Glance** is a self-hosted dashboard that consolidates all your feeds into one interface, allowing you to view everything at a glance.

## Description
After installation, a default sample configuration file is included, containing examples of various widgets. You can modify the configuration file to add or remove widgets based on your needs.
For detailed configuration instructions, refer to the [Configuration Guide](https://github.com/glanceapp/glance/blob/main/docs/configuration.md) and [Preconfigured Pages](https://github.com/glanceapp/glance/blob/main/docs/preconfigured-pages.md).

## Key Features:
- **Diverse Widgets**: Glance offers a variety of widgets, including RSS feeds, Subreddit posts, weather, bookmarks, Hacker News, Lobsters, the latest YouTube videos from specific channels, clocks, calendars, stocks, iframes, Twitch channels and top games, GitHub releases, repository overviews, website monitoring, search boxes, and more.
- **Theming**: Glance supports theming, allowing users to customize the dashboard's appearance according to personal preferences.
- **Mobile Optimization**: Glance is optimized for mobile devices, providing a great user experience on both phones and tablets.
- **Fast and Lightweight**: Glance uses minimal JavaScript, avoids bloated frameworks, and has minimal dependencies. It is distributed as a single, easy-to-deploy binary under 15MB, along with a similarly sized Docker container. All requests are parallelized, and uncached pages typically load in about 1 second (depending on internet speed and widget count).