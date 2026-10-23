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

## Security maintenance images

These 1Panel image tags backport CVE-2026-109618 / CVE-2026-109619 and are not new official PHP releases: `5.6.41` → PHP `5.6.40`, `7.0.34` → PHP `7.0.33`, `7.1.34` → PHP `7.1.33`, `7.2.35` → PHP `7.2.34`, and `7.3.34` → PHP `7.3.33`. Select them when creating a runtime; synchronizing the app store does not patch existing runtimes. Rebuild and validate the website before switching.
