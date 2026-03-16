## Usage Notes

> The default Web UI port is **80**. After the container starts, please wait approximately **60 seconds** before accessing the Web UI to allow the built-in database and application services to fully initialize.
>
> **`Frontend URL (FRONTEND_URL)`** is optional: leave it empty if accessing by IP directly; set it to the full URL (e.g. `https://your-domain.com`) if you need domain or HTTPS access — this affects CORS and OAuth2 callback configurations.
>
> Data persistence directories:
> - `./mysql` → inside container `/var/lib/mysql` (database files)
> - `./storage` → inside container `/app/storage` (configs, certs, logs, uploads, etc.)

## Product Overview

**OneClickVirt** is an extensible universal virtualization management platform that ships as a Docker all-in-one image (with Nginx frontend, Go backend, and built-in database). No external database configuration is required — simply deploy and run.

- **amd64** architecture: built-in MySQL 8.0
- **arm64** architecture: built-in MariaDB

Full documentation: [www.spiritlhl.net](https://www.spiritlhl.net/en/guide/oneclickvirt/oneclickvirt_precheck.html)

## Supported Virtualization Backends

| Type ID | Platform | Instance Types |
|---------|----------|----------------|
| `lxd` | LXD | container, vm |
| `incus` | Incus | container, vm |
| `docker` | Docker | container |
| `podman` | Podman | container |
| `containerd` | Containerd (nerdctl) | container |
| `proxmox` | Proxmox VE | container, vm |
| `qemu` | QEMU | vm |
| `kubevirt` | KubeVirt | vm |

## Key Features

- **All-in-One Deployment**: Nginx frontend, Go backend, and database are all packaged in a single image — ready out of the box
- **Multiple Backend Support**: Supports LXD, Incus, Docker, Podman, Containerd, Proxmox VE, QEMU, KubeVirt, and more
- **Web Management Interface**: Intuitive graphical interface for creating, configuring, and managing VMs/containers
- **Multi-Architecture Support**: Available for both `amd64` and `arm64` architectures with automatic adaptation
- **Data Persistence**: Database and application storage are mounted to host directories, ensuring data survives restarts
- **Flexible Access Configuration**: Supports domain and HTTPS access via the `FRONTEND_URL` environment variable
