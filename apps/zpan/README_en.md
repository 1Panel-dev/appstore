# ZPan

ZPan is an open-source, S3-native file hosting platform for web drive, file sharing, image hosting, and direct-to-object-storage workflows.

The server handles authentication, metadata, shares, quotas, team workspaces, WebDAV, and admin operations. File uploads use presigned URLs so browsers upload directly to S3-compatible storage without proxying file traffic through the application server.

After installation, open the app URL, create your first account, then go to **Admin -> Storages** to add an S3-compatible storage backend. The storage endpoint must be reachable from the browser because files upload directly from the client to object storage.

Compatible backends include Cloudflare R2, AWS S3, Backblaze B2, MinIO, RustFS, Tigris, and other S3-compatible services.
