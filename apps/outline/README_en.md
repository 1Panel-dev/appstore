
## Usage Instructions

The fastest knowledge base for growing teams. Beautiful, real-time collaboration, feature-rich, and supports Markdown format.

You can also use it as a self-hosted alternative to Feishu Docs. It is the open-source knowledge base system most similar to the Feishu Docs experience that I have seen so far.

The most troublesome part of this knowledge base is the login, which requires a dedicated OAuth application for authentication. There is no built-in login portal, so I have set Outline to use GitHub OAuth login by default. Please read the GitHub OAuth application section below before installation.

### 1. GitHub OAuth Application

1. Visit [GitHub](https://github.com/) and log in.
2. Go to the [OAuth Apps](https://github.com/settings/developers) page (or click: top right avatar - `Settings` - `Developer Settings` - `OAuth Apps`).
3. Click `New OAuth App`.
4. Fill in the `Register a new OAuth application` form:
    - `Application name`: You can fill in any name, e.g., `outline`.
    - `Homepage URL`: Enter the root `URL` of Outline, e.g., `http://your-domain.com` or `http://<your-server-ip>:<port>`.
    - `Authorization callback URL`: Enter `<Homepage URL>/auth/oidc.callback`, where `<Homepage URL>` should be replaced with the root `URL` of Outline, e.g., `http://your-domain.com/auth/oidc.callback` or `http://<your-server-ip>:<port>/auth/oidc.callback`.
5. Click the `Register application` button.

Next, obtain the `Client ID` and `Client secrets`:
1. Go to the `OAuth Apps` page (or click: top right avatar - `Settings` - `Developer Settings` - `OAuth Apps`).
2. Select the application you just created.
3. Click the `Generate a new client secret` button.
4. Note down the `Client ID` and `Client secret` (the `Client secret` is only shown once when created; if lost, you can generate a new one).

> When installing `Outline`, enter the `Client ID` and `Client secret` into:
> - `GitHub OAuth Client ID`
> - `GitHub OAuth Secret`

### 2. Reverse Proxy Domain

This is optional. If you do not have a bound domain, it is recommended to use an address like `http://<your-server-ip>:<port>` to access the Outline admin panel.

If you have configured a reverse proxy domain, please ensure your reverse proxy is correctly set up and points to the Outline server's port, otherwise you will not be able to access Outline via the domain.

### 3. SMTP Email Service Configuration (Optional)

If you want to invite members to join the Outline knowledge base via email or perform actions like password reset, you need to configure an SMTP email service.

Please fill in the correct SMTP email service information during installation. If you do not have an SMTP service, you can use some free SMTP providers or your own email provider's SMTP service.

## 4. Login

After installation, visit `http://<your-server-ip>:<port>` to log in to Outline. Normally, you will be redirected to the GitHub authorization page. After successful authorization, you will be redirected back to the Outline knowledge base homepage.

> If not redirected, you can click the `Continue with GitHub` button.


## Suggestion

Check "Allow external access to port" so you can access Outline via reverse proxy or directly by IP.
