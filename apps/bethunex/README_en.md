## Introduction

Bethune X is a cloud management platform for the full database lifecycle, covering instance onboarding, monitoring and alerting, inspection, and AI-assisted operations. It supports MySQL, Oracle, PostgreSQL, Redis, MongoDB, openGauss, DM, OceanBase and other mainstream databases.

This app is the Community Edition single-container form: the platform services, MySQL, proxy, AI Assistant and Milvus run as 42 processes inside one container, suitable for quick single-host deployment and evaluation.

## Features

- Database instance onboarding and a unified view
- Metric collection, dashboards and alert notifications
- Automated inspection and health reports
- AI Assistant for database Q&A and operations advice

Website: https://bethunex.enmotech.com/

## Requirements

- Disk ≥25G, memory ≥16G (configure in Advanced Settings → Resource Limits before installation; insufficient memory will block installation)
- First startup takes about 10-12 minutes

## Access and Default Credentials

- Platform: `http://<host IP>:8080/enmoLogin`
- Regular user: `sysuser` / `A3bDf#7Wz9`
- Admin user: `sysadmin` / `N7tK8Wz#eR4`
- MySQL: `<host IP>:3306`, root password is fixed as `dbaas@123`
- ⚠️ These are default credentials; change them immediately after installation
