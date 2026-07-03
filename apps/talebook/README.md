# TaleBook

TaleBook is a personal e-book library management system based on Calibre. It supports online reading, OPDS, multi-user access, metadata management, book import, web novel sources, e-mail push and private library mode.

## Usage

After installation, open the configured HTTP port in 1Panel to access TaleBook. The application stores library data, settings, uploaded books, logs and generated files under the mounted `data` directory.

## Notes

- This application is intended for personal book management. Do not use it to operate a public online publishing or public book library service.
- If you need to upload SSL certificates inside the container, set User ID and Group ID to `0` during installation.
- Official project: https://github.com/talebook/talebook
- User guide: https://github.com/talebook/talebook/blob/master/document/README.zh_CN.md
