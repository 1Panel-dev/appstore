# Caddy Web Server

## Product Introduction

Caddy is a modern web server with automatic HTTPS, HTTP/2, and HTTP/3 support. It is known for its simplicity, ease of use, and flexible configuration, making it particularly suitable for static website hosting, reverse proxy, and API gateway scenarios.

## Default Account Information

Caddy does not require login credentials by default and is managed through the admin API.

- **HTTP Port**: 80
- **HTTPS Port**: 443

## Key Features

### Automatic HTTPS
Caddy can automatically obtain and renew SSL/TLS certificates without manual configuration. It supports certificate authorities like Let's Encrypt and ZeroSSL, providing secure HTTPS connections for your websites.

### HTTP/2 and HTTP/3 Support
Native support for HTTP/2 and HTTP/3 protocols, providing faster web page loading speeds and better user experience. HTTP/3 is based on the QUIC protocol and can provide better performance in unstable network environments.

### Flexible Configuration Options
Supports multiple configuration methods, including Caddyfile (simplified configuration syntax) and JSON configuration. Caddyfile syntax is concise and intuitive, easy to understand and maintain.

### Reverse Proxy and Load Balancing
Powerful reverse proxy functionality with support for various load balancing algorithms, including round-robin, least connections, IP hash, etc. Easy implementation of traffic distribution for microservice architectures.

### Static File Serving
Efficient static file server with support for file compression, cache control, directory browsing, and other features. Built-in file server with excellent performance, suitable for hosting static websites.

### Plugin System
Rich plugin ecosystem supporting authentication, logging, monitoring, caching, and various other functional extensions. You can select and configure appropriate plugins as needed.

## Configuration and Usage Instructions

### Important Notes

Before using Caddy, please note the following important points:

1. **Firewall Settings**: Please ensure that the firewall allows access to the corresponding ports.
2. **Domain Resolution**: If using automatic HTTPS functionality, ensure that the domain correctly resolves to the server IP.
3. **Permission Requirements**: Caddy requires NET_ADMIN privileges to support certain network functions.

### Configuration Steps

#### Basic Configuration

1. **Create Caddyfile**: Create a `Caddyfile` configuration file in the `data/conf` directory:

```
# Simple static website configuration
example.com {
    root * /srv
    file_server
}
```

2. **Upload Website Files**: Place your website files in the `data/site` directory.

#### Reverse Proxy Configuration

Configure reverse proxy to backend services:

```
api.example.com {
    reverse_proxy localhost:8080
}
```

#### Load Balancing Configuration

Configure load balancing for multiple backend services:

```
app.example.com {
    reverse_proxy {
        to localhost:8080
        to localhost:8081
        to localhost:8082
        lb_policy round_robin
    }
}
```

#### HTTPS Configuration

Caddy will automatically apply for SSL certificates for configured domains:

```
secure.example.com {
    root * /srv
    file_server
    tls your-email@example.com
}
```

### Advanced Configuration

#### Custom Error Pages

```
example.com {
    root * /srv
    file_server
    handle_errors {
        @404 {
            expression {http.error.status_code} == 404
        }
        rewrite @404 /404.html
        file_server
    }
}
```

#### Access Log Configuration

```
example.com {
    root * /srv
    file_server
    log {
        output file /var/log/caddy/access.log
        format json
    }
}
```

#### Compression Configuration

```
example.com {
    root * /srv
    file_server
    encode gzip zstd
}
```

### Troubleshooting

1. **Configuration Syntax Errors**: Check if the Caddyfile syntax is correct, you can use the `caddy validate` command to verify.
2. **Certificate Application Failure**: Ensure domain resolution is correct, port 80 is accessible, and firewall configuration is correct.
3. **Port Conflicts**: Check if other services are occupying the same ports.
4. **Permission Issues**: Ensure Caddy has sufficient permissions to access configuration files and website directories.

Through the above configuration and instructions, you can fully utilize Caddy's powerful features to build high-performance, secure web services.

