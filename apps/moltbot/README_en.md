## Usage Guide

### 1. Open the Installation Directory

Go to the **Installed Applications** page, locate the **Moltbot** application, and click the **Open Installation Directory** button at the top.

### 2. Initialize Moltbot

Click the **Terminal** button at the top of the file list and run the following command:

```bash
docker compose -f docker-compose-cli.yml run --rm moltbot-cli onboard
```

Follow the on-screen instructions to complete the Moltbot initialization.

### 3. Update Configuration

1. After initialization, navigate to the `data/conf` directory and open the **clawdbot.json** file.
2. Add the following configuration under `gateway.controlUi.allowInsecureAuth`:

```yaml
  "gateway": {
    ***
    },
    "controlUi": {
      "allowInsecureAuth": true
    }
  },
```

Save the file after making the changes.

### 4. Obtain the Moltbot Token

Open the **clawdbot.json** file and copy the value of `"gateway.auth.token"`.  
This token will be used to access the Moltbot application.

```yaml
  "gateway": {
    "mode": "local",
    "auth": {
      "mode": "token",
      "token": "c9917c5a066beeb26266d09baed99495e7563b33c771e89a"
    },
    ***
```

### 5. Rebuild and Access the Application

1. Return to the **Installed Applications** page, locate the Moltbot application, and click **Rebuild**.
2. Once the rebuild is complete, click **Open**. In the newly opened browser tab, append `?token=YOUR_MOLTBOT_TOKEN` to the URL, for example:

```
http://your-domain-or-ip:port/?token=c9917c5a066beeb26266d09baed99495e7563b33c771e89a
```
