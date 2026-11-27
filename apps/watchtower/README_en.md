## Introduction

**Watchtower** is an open-source tool for automatically updating Docker containers. It detects whether new versions of the Docker containers you are running are available and automatically updates them, helping to maintain the security and stability of containerized applications.

## Upgrading from app store version 1.5.3

Starting with 1.24.2, the app store uses the community-maintained `nickfedor/watchtower` image. Check these settings before upgrading.

- The old version watched containers across all scopes by default. The new version defaults to containers whose scope label is missing, empty, or set to `none`. To monitor the `prod` scope, label both Watchtower and the target containers with `com.centurylinklabs.watchtower.scope=prod`. [Scope configuration guide](https://watchtower.nickfedor.com/v1.24.2/advanced-features/running-multiple-instances/)
- If your Teams notification settings include `--notification-msteams-data`, remove it from the command arguments before upgrading. This flag prevents the new version from starting. The corresponding `WATCHTOWER_NOTIFICATION_MSTEAMS_USE_LOG_DATA` environment variable also has no effect. [Teams configuration guide](https://watchtower.nickfedor.com/v1.24.2/notifications/deprecations/teams/)

## Key Features

- **Automatic Container Updates**: Watchtower periodically checks Docker Hub or other container registries for updated images. If a new version is found, it automatically downloads the new image and updates the running container.
- **Scheduled Checks**: Configure Watchtower to periodically check containers to ensure they are always up-to-date. You can set the time interval for checks.
- **Email Notifications**: Watchtower can be configured to send email notifications when containers are updated, keeping you informed of changes.
- **Flexibility**: Exclude specific containers from automatic updates to meet particular requirements.
- **Logging**: Watchtower logs container updates, allowing you to review the update history at any time.
- **Support for Multiple Container Registries**: In addition to Docker Hub, Watchtower supports other container registries, enabling you to use your preferred container images.
