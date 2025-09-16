# Caddy Web Server

## 产品介绍

Caddy 是一个现代化的 Web 服务器，具有自动 HTTPS、HTTP/2、HTTP/3 支持等特性。它以简单易用、配置灵活而著称，特别适合用于静态网站托管、反向代理和 API 网关等场景。

## 默认账户信息

Caddy 默认不需要登录凭据，通过管理 API 进行配置管理。

- **HTTP 端口**: 80
- **HTTPS 端口**: 443

## 主要功能

### 自动 HTTPS
Caddy 能够自动获取和续期 SSL/TLS 证书，无需手动配置。支持 Let's Encrypt 和 ZeroSSL 等证书颁发机构，为您的网站提供安全的 HTTPS 连接。

### HTTP/2 和 HTTP/3 支持
原生支持 HTTP/2 和 HTTP/3 协议，提供更快的网页加载速度和更好的用户体验。HTTP/3 基于 QUIC 协议，能够在不稳定的网络环境下提供更好的性能。

### 灵活的配置方式
支持多种配置方式，包括 Caddyfile（简化配置语法）和 JSON 配置。Caddyfile 语法简洁直观，易于理解和维护。

### 反向代理和负载均衡
强大的反向代理功能，支持多种负载均衡算法，包括轮询、最少连接、IP 哈希等。可以轻松实现微服务架构的流量分发。

### 静态文件服务
高效的静态文件服务器，支持文件压缩、缓存控制、目录浏览等功能。内置文件服务器性能优异，适合托管静态网站。

### 插件系统
丰富的插件生态系统，支持身份验证、日志记录、监控、缓存等各种功能扩展。可以根据需要选择和配置相应的插件。

## 配置和使用说明

### 注意事项

在使用 Caddy 之前，请注意以下重要事项：

1. **防火墙设置**: 请确保防火墙允许相应端口的访问。
2. **域名解析**: 如果使用自动 HTTPS 功能，请确保域名正确解析到服务器 IP。
3. **权限要求**: Caddy 需要 NET_ADMIN 权限以支持某些网络功能。

### 配置步骤

#### 基本配置

1. **创建 Caddyfile**: 在 `data/conf` 目录下创建 `Caddyfile` 配置文件：

```
# 简单的静态网站配置
example.com {
    root * /srv
    file_server
}
```

2. **上传网站文件**: 将您的网站文件放置在 `data/site` 目录下。

#### 反向代理配置

配置反向代理到后端服务：

```
api.example.com {
    reverse_proxy localhost:8080
}
```

#### 负载均衡配置

配置多个后端服务的负载均衡：

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

#### HTTPS 配置

Caddy 会自动为配置的域名申请 SSL 证书：

```
secure.example.com {
    root * /srv
    file_server
    tls your-email@example.com
}
```

### 高级配置

#### 自定义错误页面

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

#### 访问日志配置

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

#### 压缩配置

```
example.com {
    root * /srv
    file_server
    encode gzip zstd
}
```

### 故障排除

1. **配置语法错误**: 检查 Caddyfile 语法是否正确，可以使用 `caddy validate` 命令验证。
2. **证书申请失败**: 确保域名解析正确，80 端口可访问，防火墙配置正确。
3. **端口冲突**: 检查是否有其他服务占用了相同端口。
4. **权限问题**: 确保 Caddy 有足够的权限访问配置文件和网站目录。

通过以上配置和说明，您可以充分利用 Caddy 的强大功能，构建高性能、安全的 Web 服务。
