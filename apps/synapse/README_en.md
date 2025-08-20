## Introduction

**Synapse** is an open-source Matrix home server written and maintained by the Matrix.org Foundation.

## Instructions

- Before creating, use the following terminal command to generate the required configuration file. Modify the parameter `my.matrix.host` as needed:

```bash
docker run -it --rm \
  -v synapse-data:/data \  # Mount a volume to map the /data directory inside the container to the synapse-data volume
  -e SYNAPSE_SERVER_NAME=my.matrix.host \  # Set the public hostname of the Synapse server
  -e SYNAPSE_REPORT_STATS=no \  # Disable anonymous statistics reporting
  -e SYNAPSE_HTTP_PORT=8008 \  # Set the HTTP port Synapse listens on to 8008
  -e SYNAPSE_CONFIG_DIR=/data \  # Set the storage location for configuration files to /data
  -e SYNAPSE_DATA_DIR=/data \  # Set the storage location for persistent data to /data
  -e TZ=Asia/Shanghai \  # Set the container's timezone to Asia/Shanghai
  -e UID=1000 \  # Set the user ID running Synapse
  -e GID=1000 \  # Set the group ID running Synapse
  matrixdotorg/synapse:latest generate  # Run the latest version of the matrixdotorg/synapse image and execute the generate command to create configuration files
```

> The default storage path for configuration files is in the `synapse-data` volume at `/var/lib/docker/volumes/synapse-data/_data`.

### Create Application

### Create Users

- Create an admin account:

```bash
register_new_matrix_user  http://localhost:8008 -c /data/homeserver.yaml  -a -u <admin-username> -p <password>
```

- Create a regular user account:

```bash
register_new_matrix_user  http://localhost:8008 -c /data/homeserver.yaml   --no-admin -u <username> -p <password>
```

- View more commands and help:

```bash
register_new_matrix_user http://localhost:8008 -c /data/homeserver.yaml --help
```

### Notes

All data is stored in the `synapse-data` volume. When deleting the application, if you need to completely clear the data, you must also delete the `synapse-data` volume.
