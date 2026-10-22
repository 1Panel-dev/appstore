## Introduction

**PHP (Hypertext Preprocessor)** is a widely used server-side scripting language for developing dynamic web pages and web applications.

## Features

- **Dynamic Web Page Generation**: PHP can be embedded directly into HTML, allowing for the generation of dynamic content. This enables the creation of web pages that respond to user input or other conditions.
- **Database Support**: PHP supports a variety of database systems, including MySQL, SQLite, PostgreSQL, and more, enabling seamless database interaction and management.
- **Form Handling**: PHP can handle data from web forms, enabling input validation, data storage, and processing of user-submitted information.
- **Session Management**: PHP supports session management, allowing for the creation of login systems, shopping carts, and other user-specific functionalities.
- **File Operations**: Open, read, write, delete, upload, and download files with PHP, enabling versatile file management capabilities.
- **Error Handling**: PHP provides rich error handling features, allowing developers to capture, log, and handle errors and exceptions effectively.
- **Server-Side Execution**: PHP scripts execute on the server, generating HTML output that the client sees. This keeps the source code secure and hidden from users.
- **Cross-Platform**: PHP runs on multiple operating systems, including Windows, Linux, and macOS, making it highly versatile.
- **Open Source and Community Support**: As an open-source project, PHP has a vast developer community offering libraries, extensions, and support.
- **OOP Support**: PHP supports Object-Oriented Programming (OOP) and provides numerous built-in modules and extensions to simplify the development process.

## v1 security runtime version

`5.6.41` is a 1Panel maintenance image tag, internally PHP `5.6.40`, with backports for CVE-2026-109618 / CVE-2026-109619. It is not a new official PHP release.

The new template preserves v1's local build and mount layout using Debian-based `1panel/php:5.6.41-fpm` for amd64 only. Extensions use the base image's `install-php-extensions`, and the EOL system keeps signed Debian archive repositories. Do not reuse old binary `.so` extensions.

Synchronizing the app store does not patch existing runtimes. Create a new runtime, reinstall extensions, and test the website before switching; v1 cannot change an existing runtime's version in the edit form. This App Store template has only passed static checks; image builds, FPM requests, and extension compatibility still need validation. Maintenance does not restore official security support. The backports disable session upload progress and change allocation-failure exit behavior.
