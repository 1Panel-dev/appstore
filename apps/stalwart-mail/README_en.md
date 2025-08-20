## Introduction

**Stalwart Mail Server** is an open-source mail server solution with JMAP, IMAP4, POP3, and SMTP support and a wide range of modern features. It is written in Rust and designed to be secure, fast, robust and scalable.

## Features

- **Complete Protocol Support**: JMAP, IMAP4rev2, POP3, SMTP, and ManageSieve.
- **Advanced SMTP**: Built-in DMARC, DKIM, SPF, ARC, TLS reporting, and flexible routing & filtering.
- **Anti-Spam & Phishing Protection**: LLM-based analysis, greylisting, spam traps, and DNSBL support.
- **Flexible Backends**: Support for PostgreSQL, SQLite, Redis, S3, ElasticSearch, and more.
- **High Security**: S/MIME & OpenPGP encryption, ACME TLS certificates, rate limiting, and automatic IP blocking.
- **Scalable & Highly Available**: Kubernetes support, sharded storage, auto-recovery, no single point of failure.
- **Comprehensive Authentication**: OAuth2, OpenID Connect, LDAP, TOTP 2FA, ACLs & role-based access.
- **Monitoring & Admin Interface**: Web dashboard, Prometheus metrics, OpenTelemetry support, log viewer & alerts.

## Installation Steps

1. When installing Stalwart, make sure no other application is using the same port. If there’s a conflict (for example, with a web server like OpenResty), you can change Stalwart’s default port to another available one.

![](https://i.imgur.com/v8IiDmI.png)

2. During the initial setup, scroll down and **enable the following options**:

- **Advanced Settings**
- **External Access**

![](https://i.imgur.com/2wVMb3G.png)

3. Once the installation is complete and Stalwart is running, **check the application logs**. If they look similar to the example below, it means the installation was successful and Stalwart is now active.

![](https://i.imgur.com/QM1Euld.png)

> *Make sure to save the account information shown in the logs. You’ll need it to log in later.*

4. If your system uses a **firewall**, be sure to open the necessary ports for Stalwart using the **TCP protocol**. This allows the email server to work properly.

![](https://i.imgur.com/4aYSKN2.png)

> *In this example, port `443` is already being used by OpenResty, so I changed Stalwart’s port to `8443`.*

5. After setup is complete, open your browser and go to the address and port you selected. Use the **username and password from the logs in step 3** to log in.

![](https://i.imgur.com/0jrSZLt.png)

## Advanced Setup & Configuration

For further configuration, you can follow these two videos:

- https://youtu.be/JA_V0GFUwWc?si=71KKiAV59mUoL0X4
- https://youtu.be/PMoiJktvzDw?si=hVhQxdLuj7PJJALm
