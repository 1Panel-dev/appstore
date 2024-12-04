# Create Email Account

1. Click the **`Containers`** menu on the left.
2. On the right, click the **`Terminal`** button.
3. Run the following command to create an email account:

   ```shell
   // Create email account: 
   docker exec -ti <CONTAINER NAME> setup email add <NEW ADDRESS>
   docker exec -ti <CONTAINER_NAME> setup email add admin@example.com

# Mailserver

Mailserver is a production-ready, full-stack but simple containerized email server (SMTP, IMAP, LDAP, anti-spam, antivirus, etc.). It uses only configuration files and no SQL database. It is designed to be simple, versioned, easy to deploy and upgrade.

