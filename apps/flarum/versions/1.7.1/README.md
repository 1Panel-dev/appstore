<p align="center"><a href="https://github.com/crazy-max/docker-flarum" target="_blank"><img height="128" src="https://raw.githubusercontent.com/crazy-max/docker-flarum/master/.github/docker-flarum.jpg"></a></p>

<p align="center">
  <a href="https://hub.docker.com/r/crazymax/flarum/tags?page=1&ordering=last_updated"><img src="https://img.shields.io/github/v/tag/crazy-max/docker-flarum?label=version&style=flat-square" alt="Latest Version"></a>
  <a href="https://github.com/crazy-max/docker-flarum/actions?workflow=build"><img src="https://img.shields.io/github/actions/workflow/status/crazy-max/docker-flarum/build.yml?branch=master&label=build&logo=github&style=flat-square" alt="Build Status"></a>
  <a href="https://hub.docker.com/r/crazymax/flarum/"><img src="https://img.shields.io/docker/stars/crazymax/flarum.svg?style=flat-square&logo=docker" alt="Docker Stars"></a>
  <a href="https://hub.docker.com/r/crazymax/flarum/"><img src="https://img.shields.io/docker/pulls/crazymax/flarum.svg?style=flat-square&logo=docker" alt="Docker Pulls"></a>
  <br /><a href="https://github.com/sponsors/crazy-max"><img src="https://img.shields.io/badge/sponsor-crazy--max-181717.svg?logo=github&style=flat-square" alt="Become a sponsor"></a>
  <a href="https://www.paypal.me/crazyws"><img src="https://img.shields.io/badge/donate-paypal-00457c.svg?logo=paypal&style=flat-square" alt="Donate Paypal"></a>
</p>

## About

Docker image for [Flarum](https://flarum.org/), the next-generation forum
software that makes online discussion fun.

> **Note**
> 
> Want to be notified of new releases? Check out 🔔 [Diun (Docker Image Update Notifier)](https://github.com/crazy-max/diun)
> project!

___

- [About](#about)
- [Features](#features)
- [Build locally](#build-locally)
- [Image](#image)
- [Environment variables](#environment-variables)
  - [General](#general)
  - [Flarum](#flarum)
  - [Database](#database)
- [Volumes](#volumes)
- [Ports](#ports)
- [Usage](#usage)
  - [Docker Compose](#docker-compose)
  - [Command line](#command-line)
- [Upgrade](#upgrade)
- [Notes](#notes)
  - [First launch](#first-launch)
  - [Manage extensions](#manage-extensions)
  - [Sending mails with SMTP](#sending-mails-with-smtp)
- [Contributing](#contributing)
- [License](#license)

## Features

* Run as non-root user
* Multi-platform image
* [s6-overlay](https://github.com/just-containers/s6-overlay/) as process supervisor
* [msmtpd SMTP relay](https://github.com/crazy-max/docker-msmtpd) image to send emails
* [Traefik](https://github.com/containous/traefik-library-image) as reverse proxy and creation/renewal of Let's Encrypt certificates (see [this template](examples/traefik))

## Build locally

```shell
git clone https://github.com/crazy-max/docker-flarum.git
cd docker-flarum

# Build image and output to docker (default)
docker buildx bake

# Build multi-platform image
docker buildx bake image-all
```

## Image

| Registry                                                                                         | Image                           |
|--------------------------------------------------------------------------------------------------|---------------------------------|
| [Docker Hub](https://hub.docker.com/r/crazymax/flarum/)                                            | `crazymax/flarum`                 |
| [GitHub Container Registry](https://github.com/users/crazy-max/packages/container/package/flarum)  | `ghcr.io/crazy-max/flarum`        |

Following platforms for this image are available:

```
$ docker run --rm mplatform/mquery crazymax/flarum:latest
Image: crazymax/flarum:latest
 * Manifest List: Yes
 * Supported platforms:
   - linux/amd64
   - linux/arm/v6
   - linux/arm/v7
   - linux/arm64
```

## Environment variables

### General

* `TZ`: The timezone assigned to the container (default `UTC`)
* `PUID`: Flarum user id (default `1000`)
* `PGID`: Flarum group id (default `1000`)
* `MEMORY_LIMIT`: PHP memory limit (default `256M`)
* `UPLOAD_MAX_SIZE`: Upload max size (default `16M`)
* `CLEAR_ENV`: Clear environment in FPM workers (default `yes`)
* `OPCACHE_MEM_SIZE`: PHP OpCache memory consumption (default `128`)
* `LISTEN_IPV6`: Enable IPv6 for Nginx (default `true`)
* `REAL_IP_FROM`: Trusted addresses that are known to send correct replacement addresses (default `0.0.0.0/32`)
* `REAL_IP_HEADER`: Request header field whose value will be used to replace the client address (default `X-Forwarded-For`)
* `LOG_IP_VAR`: Use another variable to retrieve the remote IP address for access [log_format](http://nginx.org/en/docs/http/ngx_http_log_module.html#log_format) on Nginx. (default `remote_addr`)

### Flarum

* `FLARUM_DEBUG`: Enables or disables debug mode, used to troubleshoot issues (default `false`)
* `FLARUM_BASE_URL`: The URL to your Flarum installation **required**
* `FLARUM_FORUM_TITLE`: Flarum forum title, only used during first installation (default `Flarum Dockerized`)
* `FLARUM_API_PATH`: Flarum api path (default `api`)
* `FLARUM_ADMIN_PATH`: Flarum admin path (default `admin`)
* `FLARUM_POWEREDBY_HEADER`: Set Flarum's `X-Powered-By` header (default `true`)
* `FLARUM_REFERRER_POLICY`: Referrer policy (default `same-origin`)
* `FLARUM_COOKIE_SAMESITE`: Set `SameSite` attribute of `Set-Cookie` (default `lax`)

### Database

* `DB_HOST`: MySQL database hostname / IP address **required**
* `DB_PORT`: MySQL database port (default `3306`)
* `DB_NAME`: MySQL database name (default `flarum`)
* `DB_USER`: MySQL user (default `flarum`)
* `DB_PASSWORD`: MySQL password
* `DB_PREFIX`: MySQL database prefix (default `flarum_`)
* `DB_NOPREFIX`: Enforce no prefix for the MySQL database (default `false`)
* `DB_TIMEOUT`: Time in seconds after which we stop trying to reach the MySQL server (useful for clusters, default `60`)

> 💡 `DB_USER_FILE` and `DB_PASSWORD_FILE` can be used to fill in the value from a file, especially for Docker's
> secrets feature.

## Volumes

* `/data`: Contains assets, extensions and storage

> :warning: Note that the volume should be owned by the user/group with the specified `PUID` and `PGID`. If you
> don't give the volume correct permissions, the container may not start.

## Ports

* `8000`: HTTP port

## Usage

### Docker Compose

Docker compose is the recommended way to run this image. You can use the following
[docker compose template](examples/compose/docker-compose.yml), then run the container:

```bash
docker-compose up -d
docker-compose logs -f
```

### Command line

You can also use the following minimal command:

```bash
docker run -d -p 8000:8000 --name flarum \
  -v $(pwd)/data:/data \
  -e "DB_HOST=db" \
  -e "FLARUM_BASE_URL=http://127.0.0.1:8000" \
  crazymax/flarum:latest
```

> `-e "DB_HOST=db"`<br />
> :warning: `db` must be a running MySQL instance

## Upgrade

You can upgrade Flarum automatically through the UI, it works well. But I recommend to recreate the container
whenever I push an update:

```bash
docker-compose pull
docker-compose up -d
```

## Notes

### First launch

On first launch, an initial administrator user will be created:

| Login    | Password |
|----------|----------|
| `flarum` | `flarum` |

### Manage extensions

You can install [Flarum extensions](https://extiverse.com/) from the command line using a
[specially crafted script](rootfs/usr/local/bin/extension) with this image:

`docker-compose exec flarum extension require <package>`

To remove an extension:

`docker-compose exec flarum extension remove <package>`

To list all extensions:

`docker-compose exec flarum extension list`

Example with [`fof/upload`](https://extiverse.com/extension/fof/upload) extension:

```
$ docker-compose exec flarum extension require fof/upload
Using version ^1.0 for fof/upload
./composer.json has been updated
Running composer update fof/upload
Loading composer repositories with package information
Updating dependencies
Lock file operations: 5 installs, 0 updates, 0 removals
  - Locking fof/upload (1.0.0)
  - Locking guzzlehttp/guzzle (7.3.0)
  - Locking guzzlehttp/promises (1.4.1)
  - Locking psr/http-client (1.0.1)
  - Locking softcreatr/php-mime-detector (3.2.0)
Writing lock file
Installing dependencies from lock file (including require-dev)
Package operations: 5 installs, 0 updates, 0 removals
  - Downloading softcreatr/php-mime-detector (3.2.0)
  - Downloading psr/http-client (1.0.1)
  - Downloading guzzlehttp/promises (1.4.1)
  - Downloading guzzlehttp/guzzle (7.3.0)
  - Downloading fof/upload (1.0.0)
  - Installing softcreatr/php-mime-detector (3.2.0): Extracting archive
  - Installing psr/http-client (1.0.1): Extracting archive
  - Installing guzzlehttp/promises (1.4.1): Extracting archive
  - Installing guzzlehttp/guzzle (7.3.0): Extracting archive
  - Installing fof/upload (1.0.0): Extracting archive
2 package suggestions were added by new dependencies, use `composer suggest` to see details.
Generating autoload files
70 packages you are using are looking for funding.
Use the `composer fund` command to find out more!
fof/upload extension added
Clearing the cache...
```

> :warning: You cannot use [Bazaar marketplace extension](https://discuss.flarum.org/d/5151-bazaar-the-extension-marketplace)
> to install extensions for now.

### Sending mails with SMTP

You can use our SMTP relay `msmtpd` service published on port `2500` and declared in our [`docker-compose.yml`](examples/compose/docker-compose.yml):

![](.github/smtp-settings.png)

## Contributing

Want to contribute? Awesome! The most basic way to show your support is to star the project, or to raise issues. You
can also support this project by [**becoming a sponsor on GitHub**](https://github.com/sponsors/crazy-max) or by making
a [Paypal donation](https://www.paypal.me/crazyws) to ensure this journey continues indefinitely!

Thanks again for your support, it is much appreciated! :pray:

## License

MIT. See `LICENSE` for more details.

