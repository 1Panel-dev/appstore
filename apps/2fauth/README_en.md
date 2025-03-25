# Setup

## **Enable HTTPS**

- **Create Application**: Set the external address to `https://2fauth.example.com` (please replace the domain name according to your actual situation).
- **Create Reverse Proxy Site**: Set the proxy address to `http://127.0.0.1:8000` (please replace the IP and port according to your actual situation).
- **Apply for Certificate**: Create a certificate for `2fauth.example.com`, choose HTTP as the verification method.
- **Enable HTTPS**: Go to the website settings page, click on HTTPS, select the certificate you just added and save.

## **Without HTTPS**

- If HTTPS is not enabled, it will cause issues such as inability to scan QR codes. In this case, the external address can be directly set to `http://192.168.10.100:8000` without the need for reverse proxy settings.

