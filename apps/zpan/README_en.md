# ZPan

ZPan is open-source file hosting for S3-compatible storage, covering web drive, file sharing, image hosting, WebDAV, and direct-to-object-storage workflows.

The server handles authentication, metadata, shares, quotas, team workspaces, WebDAV, and admin operations. File uploads use presigned URLs so browsers upload directly to S3-compatible storage without proxying file traffic through the application server.

This app deploys both the ZPan web service and a downloader node. On first start, the downloader prints a device authorization URL in the container logs. Open that URL as an admin user to register the downloader, then it can handle remote-download tasks.

After installation, open the app URL, create your first account, then go to **Admin -> Storages** to add an S3-compatible storage backend. The storage endpoint must be reachable from the browser because files upload directly from the client to object storage.

Compatible backends include Cloudflare R2, AWS S3, Backblaze B2, MinIO, RustFS, Tigris, and other S3-compatible services.
