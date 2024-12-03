# SFTPGo

**SFTPGo** is a fully-featured and highly configurable SFTP server that also supports custom HTTP/S, FTP/S, and WebDAV services.

> Supported backend storage: Local filesystem, encrypted local filesystem, S3-compatible object storage, Google Cloud Storage, Azure Blob Storage, and SFTP.

## Main Features:

- **Multi-User Support**: Create and manage multiple user accounts, each with independent access permissions and directories.
- **Highly Configurable**: Customize extensively to meet your needs, including user access control, upload/download limits, and authentication methods.
- **Security**: Supports multiple authentication methods like SSH keys, username/password, and multifactorial authentication. IP whitelisting and blacklisting enhance security further.
- **File Transfer Management**: Securely upload and download files using SFTP and SCP protocols. Features include transfer rate limits, folder quotas, and file type filtering.
- **High Performance**: Optimized for handling high concurrency and throughput in file transfers.
- **Monitoring and Logging**: Offers detailed logging and monitoring capabilities for administrators to track user activity and server performance.
- **Automation**: Integrates into workflows via REST API and Webhook support for streamlined file transfer management.
- **Extensibility**: You can increase the functionality of SFTPGo through plugins and extensions to meet specific requirements.