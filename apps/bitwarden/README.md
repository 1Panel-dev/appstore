### Alternative implementation of the Bitwarden server API written in Rust and compatible with [upstream Bitwarden clients](https://bitwarden.com/download/)*, perfect for self-hosted deployment where running the official resource-heavy service might not be ideal.

---

## Features

Basically full implementation of Bitwarden API is provided including:

 * Organizations support
 * Attachments and Send
 * Vault API support
 * Serving the static files for Vault interface
 * Website icons API
 * Authenticator and U2F support
 * YubiKey and Duo support
 * Emergency Access

## Installation
Pull the docker image and mount a volume from the host for persistent storage:

```sh
docker pull vaultwarden/server:latest
docker run -d --name vaultwarden -v /vw-data/:/data/ -p 80:80 vaultwarden/server:latest
```
This will preserve any persistent data under /vw-data/, you can adapt the path to whatever suits you.

**IMPORTANT**: Most modern web browsers, disallow the use of Web Crypto APIs in insecure contexts. In this case, you might get an error like `Cannot read property 'importKey'`. To solve this problem, you need to access the web vault via HTTPS or localhost.

This can be configured in [vaultwarden directly](https://github.com/dani-garcia/vaultwarden/wiki/Enabling-HTTPS) or using a third-party reverse proxy ([some examples](https://github.com/dani-garcia/vaultwarden/wiki/Proxy-examples)).

If you have an available domain name, you can get HTTPS certificates with [Let's Encrypt](https://letsencrypt.org/), or you can generate self-signed certificates with utilities like [mkcert](https://github.com/FiloSottile/mkcert). Some proxies automatically do this step, like Caddy (see examples linked above).

## Usage
See the [vaultwarden wiki](https://github.com/dani-garcia/vaultwarden/wiki) for more information on how to configure and run the vaultwarden server.

## Get in touch
To ask a question, offer suggestions or new features or to get help configuring or installing the software, please use [GitHub Discussions](https://github.com/dani-garcia/vaultwarden/discussions) or [the forum](https://vaultwarden.discourse.group/).

If you spot any bugs or crashes with vaultwarden itself, please [create an issue](https://github.com/dani-garcia/vaultwarden/issues/). Make sure you are on the latest version and there aren't any similar issues open, though!

If you prefer to chat, we're usually hanging around at [#vaultwarden:matrix.org](https://matrix.to/#/#vaultwarden:matrix.org) room on Matrix. Feel free to join us!
