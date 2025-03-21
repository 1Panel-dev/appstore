# Create Database
After deploying CouchDB successfully, you need to manually create a database for the plugin to connect and sync. Visit `http://localhost:5984/_utils`, log in with your credentials, and click `Create Database` to create a database as per your preference.

> If you want to access Self-hosted LiveSync from a mobile device, you need a valid SSL certificate.

# Self-hosted LiveSync
**Self-hosted LiveSync** is a community-implemented online sync plugin. It uses a self-hosted or purchased CouchDB as a middleware server and is compatible with all platforms supported by Obsidian.

> This plugin is not compatible with the official `Obsidian Sync` service.

## Features:
- Visual conflict resolver
- Near real-time bidirectional sync across multiple devices
- Supports CouchDB and compatible services like IBM Cloudant
- End-to-end encryption
- Plugin sync (Beta)
- Receive WebClip from [obsidian-livesync-webclip](https://chrome.google.com/webstore/detail/obsidian-livesync-webclip/jfpaflmpckblieefkegjncjoceapakdf) (not applicable for end-to-end encryption)

Ideal for researchers, engineers, or developers who need complete autonomy over their notes for security reasons, as well as anyone who values the privacy of their notes.

> For detailed usage, refer to the official documentation.