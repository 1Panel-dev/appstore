# Bitnami package for OpenLDAP

## What is OpenLDAP?

> OpenLDAP is the open-source solution for LDAP (Lightweight Directory Access Protocol). It is a protocol used to  store and retrieve data from a hierarchical directory structure such as in databases.

[Overview of OpenLDAP](https://openldap.org/)
Trademarks: This software listing is packaged by Bitnami. The respective trademarks mentioned in the offering are owned by the respective companies, and use of them does not imply any affiliation or endorsement.

## Configuration

The Bitnami Docker OpenLDAP can be easily setup with the following environment variables:

* `LDAP_PORT_NUMBER`: The port OpenLDAP is listening for requests. Priviledged port is supported (e.g. `389`). Default: **1389** (non privileged port).
* `LDAP_ROOT`: LDAP baseDN (or suffix) of the LDAP tree. Default: **dc=example,dc=org**
* `LDAP_ADMIN_USERNAME`: LDAP database admin user. Default: **admin**
* `LDAP_ADMIN_PASSWORD`: LDAP database admin password. Default: **adminpassword**
* `LDAP_ADMIN_PASSWORD_FILE`: Path to a file that contains the LDAP database admin user password. This will override the value specified in `LDAP_ADMIN_PASSWORD`. No defaults.
* `LDAP_CONFIG_ADMIN_ENABLED`: Whether to create a configuration admin user. Default: **no**.
* `LDAP_CONFIG_ADMIN_USERNAME`: LDAP configuration admin user. This is separate from `LDAP_ADMIN_USERNAME`. Default: **admin**.
* `LDAP_CONFIG_ADMIN_PASSWORD`: LDAP configuration admin password. Default: **configpassword**.
* `LDAP_CONFIG_ADMIN_PASSWORD_FILE`: Path to a file that contains the LDAP configuration admin user password. This will override the value specified in `LDAP_CONFIG_ADMIN_PASSWORD`. No defaults.
* `LDAP_USERS`: Comma separated list of LDAP users to create in the default LDAP tree. Default: **user01,user02**
* `LDAP_PASSWORDS`: Comma separated list of passwords to use for LDAP users. Default: **bitnami1,bitnami2**
* `LDAP_USER_DC`: DC for the users' organizational unit. Default: **users**
* `LDAP_GROUP`: Group used to group created users. Default: **readers**
* `LDAP_ADD_SCHEMAS`: Whether to add the schemas specified in `LDAP_EXTRA_SCHEMAS`. Default: **yes**
* `LDAP_EXTRA_SCHEMAS`: Extra schemas to add, among OpenLDAP's distributed schemas. Default: **cosine, inetorgperson, nis**
* `LDAP_SKIP_DEFAULT_TREE`: Whether to skip creating the default LDAP tree based on `LDAP_USERS`, `LDAP_PASSWORDS`, `LDAP_USER_DC` and `LDAP_GROUP`. Please note that this will **not** skip the addition of schemas or importing of LDIF files. Default: **no**
* `LDAP_CUSTOM_LDIF_DIR`: Location of a directory that contains LDIF files that should be used to bootstrap the database. Only files ending in `.ldif` will be used. Default LDAP tree based on the `LDAP_USERS`, `LDAP_PASSWORDS`, `LDAP_USER_DC` and `LDAP_GROUP` will be skipped when `LDAP_CUSTOM_LDIF_DIR` is used. When using this it will override the usage of `LDAP_USERS`, `LDAP_PASSWORDS`, `LDAP_USER_DC` and `LDAP_GROUP`. You should set `LDAP_ROOT` to your base to make sure the `olcSuffix` configured on the database matches the contents imported from the LDIF files. Default: **/ldifs**
* `LDAP_CUSTOM_SCHEMA_FILE`: Location of a custom internal schema file that could not be added as custom ldif file (i.e. containing some `structuralObjectClass`). Default is **/schema/custom.ldif**"
* `LDAP_CUSTOM_SCHEMA_DIR`: Location of a directory containing custom internal schema files that could not be added as custom ldif files (i.e. containing some `structuralObjectClass`). This can be used in addition to or instead of `LDAP_CUSTOM_SCHEMA_FILE` (above) to add multiple schema files. Default: **/schemas**
* `LDAP_ULIMIT_NOFILES`: Maximum number of open file descriptors. Default: **1024**.
* `LDAP_ALLOW_ANON_BINDING`: Allow anonymous bindings to the LDAP server. Default: **yes**.
* `LDAP_LOGLEVEL`: Set the loglevel for the OpenLDAP server (see <https://www.openldap.org/doc/admin26/slapdconfig.html> for possible values). Default: **256**.
* `LDAP_PASSWORD_HASH`: Hash to be used in generation of user passwords. Must be one of {SSHA}, {SHA}, {SMD5}, {MD5}, {CRYPT}, and {CLEARTEXT}. Default: **{SSHA}**.
* `LDAP_CONFIGURE_PPOLICY`: Enables the ppolicy module and creates an empty configuration. Default: **no**.
* `LDAP_PPOLICY_USE_LOCKOUT`: Whether bind attempts to locked accounts will always return an error. Will only be applied with `LDAP_CONFIGURE_PPOLICY` active. Default: **no**.
* `LDAP_PPOLICY_HASH_CLEARTEXT`: Whether plaintext passwords should be hashed automatically. Will only be applied with `LDAP_CONFIGURE_PPOLICY` active. Default: **no**.

You can bootstrap the contents of your database by putting LDIF files in the directory `/ldifs` (or the one you define in `LDAP_CUSTOM_LDIF_DIR`). Those may only contain content underneath your base DN (set by `LDAP_ROOT`). You can **not** set configuration for e.g. `cn=config` in those files.

Check the official [OpenLDAP Configuration Reference](https://www.openldap.org/doc/admin26/guide.html) for more information about how to configure OpenLDAP.

### Overlays

Overlays are dynamic modules that can be added to an OpenLDAP server to extend or modify its functionality.

#### Access Logging

This overlay can record accesses to a given backend database on another database.

* `LDAP_ENABLE_ACCESSLOG`: Enables the accesslog module with the following configuration defaults unless specified otherwise. Default: **no**.
* `LDAP_ACCESSLOG_ADMIN_USERNAME`: Admin user for accesslog database. Default: **admin**.
* `LDAP_ACCESSLOG_ADMIN_PASSWORD`: Admin password for accesslog database. Default: **accesspassword**.
* `LDAP_ACCESSLOG_DB`: The DN (Distinguished Name) of the database where the access log entries will be stored. Will only be applied with `LDAP_ENABLE_ACCESSLOG` active. Default: **cn=accesslog**.
* `LDAP_ACCESSLOG_LOGOPS`: Specify which types of operations to log. Valid aliases for common sets of operations are: writes, reads, session or all. Will only be applied with `LDAP_ENABLE_ACCESSLOG` active. Default: **writes**.
* `LDAP_ACCESSLOG_LOGSUCCESS`: Whether successful operations should be logged. Will only be applied with `LDAP_ENABLE_ACCESSLOG` active. Default: **TRUE**.
* `LDAP_ACCESSLOG_LOGPURGE`: When and how often old access log entries should be purged. Format `"dd+hh:mm"`. Will only be applied with `LDAP_ENABLE_ACCESSLOG` active. Default: **07+00:00 01+00:00**.
* `LDAP_ACCESSLOG_LOGOLD`: An LDAP filter that determines which entries should be logged. Will only be applied with `LDAP_ENABLE_ACCESSLOG` active. Default: **(objectClass=*)**.
* `LDAP_ACCESSLOG_LOGOLDATTR`: Specifies an attribute that should be logged. Will only be applied with `LDAP_ENABLE_ACCESSLOG` active. Default: **objectClass**.

Check the official page [OpenLDAP, Overlays, Access Logging](https://www.openldap.org/doc/admin26/overlays.html#Access%20Logging) for detailed configuration information.

#### Sync Provider

* `LDAP_ENABLE_SYNCPROV`: Enables the syncrepl module with the following configuration defaults unless specified otherwise. Default: **no**.
* `LDAP_SYNCPROV_CHECKPPOINT`: For every 100 operations or 10 minutes, which ever is sooner, the contextCSN will be checkpointed. Will only be applied with `LDAP_ENABLE_SYNCPROV` active. Default: **100 10**.
* `LDAP_SYNCPROV_SESSIONLOG`: The maximum number of session log entries the session log can record. Will only be applied with `LDAP_ENABLE_SYNCPROV` active. Default: **100**.

Check the official page [OpenLDAP, Overlays, Sync Provider](https://www.openldap.org/doc/admin26/overlays.html#Sync%20Provider) for detailed configuration information.

### Securing OpenLDAP traffic

OpenLDAP clients and servers are capable of using the Transport Layer Security (TLS) framework to provide integrity and confidentiality protections and to support LDAP authentication using the SASL EXTERNAL mechanism. Should you desire to enable this optional feature, you may use the following environment variables to configure the application:

* `LDAP_ENABLE_TLS`: Whether to enable TLS for traffic or not. Defaults to `no`.
* `LDAP_REQUIRE_TLS`: Whether connections must use TLS. Will only be applied with `LDAP_ENABLE_TLS` active. Defaults to `no`.
* `LDAP_LDAPS_PORT_NUMBER`: Port used for TLS secure traffic. Priviledged port is supported (e.g. `636`). Default: **1636** (non privileged port).
* `LDAP_TLS_CERT_FILE`: File containing the certificate file for the TLS traffic. No defaults.
* `LDAP_TLS_KEY_FILE`: File containing the key for certificate. No defaults.
* `LDAP_TLS_CA_FILE`: File containing the CA of the certificate. No defaults.
* `LDAP_TLS_DH_PARAMS_FILE`: File containing the DH parameters. No defaults.

## License

Copyright &copy; 2024 Broadcom. The term "Broadcom" refers to Broadcom Inc. and/or its subsidiaries.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

<http://www.apache.org/licenses/LICENSE-2.0>

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.