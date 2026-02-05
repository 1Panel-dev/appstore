## Default Credentials

username: `admin`

password: Go to the App Store and get it from the app's parameter settings

## Configuration and Usage Instructions

-   **admin** is a temporary administrator account. After the initial login, please create a new account, **assign it the administrator role**, and then delete the **admin** account.
    1.  Find **Users** in the left sidebar, click **Add Users** to create an account.
    2.  Click on the new account to enter **User details**, select the **Role mapping** tab, click **Assign role**, and choose **Realm roles**.
    3.  Check the box for **admin (role_admin)** and click **Assign**.
    4.  Delete the **admin** account.
-   Keycloak supports i18n, but it needs to be enabled manually.
    -   Find **Realm settings** in the left sidebar, select the **Localization** tab, enable **Internationalization**, and then select your supported locale(s).

## Introduction

Keycloak is an open-source **Identity and Access Management (IAM)** platform designed for modern applications and services. Its core objective is to simplify the authentication and authorization processes for applications, freeing developers from the complexities of handling user storage, authentication logic, and other intricate security concerns.

## Features

-   **Support for Standardized Identity Protocols**: Natively supports mainstream identity protocols such as OpenID Connect, OAuth 2.0, and SAML, enabling applications to easily integrate Single Sign-On (SSO) and social logins (e.g., Google, GitHub).
-   **User Federation and Centralized Management**: Allows synchronization of users from external user stores (e.g., LDAP, Active Directory, Kerberos) and provides a unified user management interface, simplifying user lifecycle management (creation, update, deletion, role assignment).
-   **Fine-Grained Authorization and Access Control**: Offers detailed authorization policies based on roles (RBAC) and attributes (ABAC), supports dynamic permission management and policy decision-making, ensuring secure access to applications and services.
-   **Strong Authentication Mechanisms**: Built-in Multi-Factor Authentication (MFA), conditional access, password policies, and customizable authentication flows enhance account security and meet compliance requirements.