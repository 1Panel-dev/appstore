## Introduction

CyberChef, officially described as "The Cyber Swiss Army Knife", is a browser-based data processing toolkit. It is well suited for encoding, decoding, encryption, decryption, compression, hashing, format conversion, and forensic-style analysis without writing scripts.

## Features

- Supports operations such as Base64, XOR, AES, DES, and Blowfish.
- Supports compression, decompression, hashes, checksums, binary dumps, and hex dumps.
- Supports parsing and converting IPv6, X.509, and character encodings.
- Supports drag and drop, Auto Bake, breakpoints, and saving/loading recipes.
- Most processing happens locally in the browser, making it suitable for offline or internal-network use.

## Access After Installation

After installation, visit `http://<ServerIP>:<PANEL_APP_PORT_HTTP>`.

CyberChef does not require an initial account setup and can be used directly.

## Data Storage

The current 1Panel template is stateless. If you need to preserve recipes, you can rely on browser local storage or extend the deployment according to the official documentation.

## Notes

The official project is still actively evolving. It is best suited for analysis and transformation workflows and should not be treated as a substitute for security-audited cryptographic products.

## Official Links

- Live Demo: https://gchq.github.io/CyberChef/
- Documentation: https://github.com/gchq/CyberChef/wiki
- Source Code: https://github.com/gchq/CyberChef
