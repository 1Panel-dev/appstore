# Fizzy

Fizzy is a Kanban project management tool from 37signals for organizing issues,
ideas, and team work. This package uses the official Fizzy Docker image and
stores its databases, queues, cache, and attachments in the installation's
`data` directory.

## Installation and first sign-in

1. Choose an unused HTTP port.
2. Set `BASE_URL` to the complete URL that browsers use to reach Fizzy.
3. 1Panel generates `SECRET_KEY_BASE`; do not change it after installation.
4. A working SMTP configuration is required. Fizzy uses email verification
   codes for sign-up and sign-in, so users cannot complete email-code login
   until mail delivery works.

## SMTP

Fizzy does not provide an SMTP server configuration page in its own
administration UI. In the Fizzy application parameters in 1Panel, enter the
sender address, SMTP server, and port; provide the username, password, and TLS
option as required by your SMTP provider. These settings may be edited later in
1Panel, but restart the application after every change; users cannot complete
email-code login until mail delivery works.

Set `SMTP_TLS=true` only for an SMTP server that requires implicit TLS, usually
on port `465`. Most servers using STARTTLS should keep `SMTP_TLS=false`, usually
on port `587`.

## Domain and HTTPS

The Fizzy container serves HTTP only. Create a reverse-proxy website and manage
the HTTPS certificate in 1Panel. After enabling the domain, change `BASE_URL` to
the final address, such as `https://fizzy.example.com`, and restart the
application.

## Data and backups

All persistent content is in the installation's `data` directory, mounted at
`/rails/storage` inside the container. Stop the application before a backup,
copy the complete directory, and preserve write access for UID/GID `1000:1000`
when restoring it.

## Version policy

This application uses the rolling image `ghcr.io/basecamp/fizzy:latest`.
Upstream changes can take effect after the image is pulled again, so back up the
`data` directory before updating.

## License

Fizzy's source is available under the O'Saasy License. It permits self-hosting,
modification, and distribution, but restricts offering Fizzy as a competing
hosted or SaaS service. It is not an OSI-approved open-source license; read the
upstream `LICENSE.md` before deployment.
