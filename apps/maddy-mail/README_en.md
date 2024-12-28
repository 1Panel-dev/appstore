# User Guide

## 1. Prepare Domain Certificates

Prepare a domain certificate using tools like `acme.sh`, `certbot`, or manual upload. Modify the configurations as needed.  

The certificate domain should correspond to the mail server's `MX` hostname, such as `mail.example.com`.


## 2. Install the Application

Install the application from the app store.

The first installation may show an error and the container may not run properly due to missing certificate files. 

Ignore the error and proceed to the next step.


## 3. Place the Domain Certificate into the Volume

If the maddydata directory is not present in the container storage volume, you need to manually execute:
```
docker volume create maddydata 
```

The default path for the volume is:`/var/lib/docker/volumes/maddydata/_data/`

```
# Enter the volume path
cd $(docker volume inspect maddydata --format '{{.Mountpoint}}')

# Create certificate folder
mkdir -p tls
```
Upload the certificate and private key to the tls folder, and rename them as:
- fullchain.pem
- privkey.pem

Once the certificates are correctly uploaded, the container will automatically start running.

## 4. Configure DKIM DNS Records
### 4.1 Retrieve DKIM Values

After the container starts running, check the path:`/var/lib/docker/volumes/maddydata/_data/dkim_keys`

You will find a file named like `example.com_default.dns`.

This file contains the required DKIM information.

- Make sure to modify the domain as needed.

To view the contents in the terminal:
```
cat /var/lib/docker/volumes/maddydata/_data/dkim_keys/example.com_default.dns 
```

Example output:
```
default._domainkey.example.org.    TXT   "v=DKIM1; k=ed25519; p=nAcUUozPlhc4VPhp7hZl+owES7j7OlEv0laaDEDBAqg="
```

### 4.2  Set DNS TXT Record

Set the `DNS records` based on the retrieved information.

For example:
Add a `TXT` record for `default._domainkey.example.com` with the value `v=DKIM1; k=ed25519; p=nAcUUozPlhc4VPhp7hZl+owES7j7OlEv0laaDEDBAqg=`.

## 5. Set DNS Records

- Ensure modifications as needed.

| Record Type | Domain  | Value                                                       |
|-------------| --- |-------------------------------------------------------------|
| A           | `mail.example.com` | `Server IPv4 Address`                                       |
| A           | `example.com` | `Server IPv4 Address`                                       |
| AAAA        | `mail.example.com` | `Server IPv4 Address (if available)`                        |
| AAAA        | `example.com` | `Server IPv4 Address (if available)`                        |
| MX          | `example.com` | `mail.example.com`                                          |
| TXT         | `mail.example.com` | `v=spf1 mx ~all`                                            |
| TXT         | `example.com` | `v=spf1 mx ~all`                                            |
| TXT         | `_dmarc.example.com` | `v=DMARC1; p=quarantine; ruf=mailto:postmaster@example.com` |
| TXT         | `_mta-sts.example.com` | `v=STSv1; id=1`                                             |
| TXT         | `_smtp._tls.example.com` | `v=TLSRPTv1;rua=mailto:postmaster@example.com`              |

## 6. Create Sending Accounts

Access the container terminal via the `Containers` panel and execute the following commands:

- Ensure modifications as needed.

```
maddy creds create postmaster@example.com

maddy imap-acct create postmaster@example.com 
```
END
