## Instructions

- **Create Application**: The Logto admin console address must be `https://admin.domain.com` (replace `domain.com` with your actual domain).
- **Create Reverse Proxy Website**: Set the proxy address to `http://127.0.0.1:3002` (replace IP and port as needed).
- **Apply for Certificate**: Create a certificate for `https://admin.domain.com` and select HTTP verification.
- **Enable HTTPS**: Go to the website settings page, click HTTPS, select the newly added certificate, and save.

> The Logto service address and admin console address must use the HTTPS protocol to function correctly.
