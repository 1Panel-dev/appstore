# 使用说明

控制台默认账户密码
```
Email:    admin@example.com
Password: changeme
```


# 原始相关

This project comes as a pre-built docker image that enables you to easily forward to your websites
running at home or otherwise, including free SSL, without having to know too much about Nginx or Letsencrypt.

## Project Goal

I created this project to fill a personal need to provide users with a easy way to accomplish reverse proxying hosts with SSL termination and it had to be so easy that a monkey could do it. This goal hasn't changed. While there might be advanced options they are optional and the project should be as simple as possible so that the barrier for entry here is low.

## Features

- Beautiful and Secure Admin Interface based on [Tabler](https://tabler.github.io/)
- Easily create forwarding domains, redirections, streams and 404 hosts without knowing anything about Nginx
- Free SSL using Let's Encrypt or provide your own custom SSL certificates
- Access Lists and basic HTTP Authentication for your hosts
- Advanced Nginx configuration available for super users
- User management, permissions and audit log

## Hosting your home network

I won't go in to too much detail here but here are the basics for someone new to this self-hosted world.

1. Your home router will have a Port Forwarding section somewhere. Log in and find it
2. Add port forwarding for port 80 and 443 to the server hosting this project
3. Configure your domain name details to point to your home, either with a static ip or a service like DuckDNS or [Amazon Route53](https://github.com/jc21/route53-ddns)
4. Use the Nginx Proxy Manager as your gateway to forward to your other web based services
