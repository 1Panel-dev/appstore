## Purpose

The goal of this project is to make the easiest, fastest, and most
painless way of setting up a self-hosted Git service.

As Gitea is written in Go, it works across **all** the platforms and
architectures that are supported by Go, including Linux, macOS, and
Windows on x86, amd64, ARM and PowerPC architectures.
You can try it out using [the online demo](https://try.gitea.io/).
This project has been
[forked](https://blog.gitea.io/2016/12/welcome-to-gitea/) from
[Gogs](https://gogs.io) since November of 2016, but a lot has changed.

## Building

From the root of the source tree, run:

    TAGS="bindata" make build

or if SQLite support is required:

    TAGS="bindata sqlite sqlite_unlock_notify" make build

The `build` target is split into two sub-targets:

- `make backend` which requires [Go Stable](https://go.dev/dl/), required version is defined in [go.mod](/go.mod).
- `make frontend` which requires [Node.js LTS](https://nodejs.org/en/download/) or greater and Internet connectivity to download npm dependencies.

When building from the official source tarballs which include pre-built frontend files, the `frontend` target will not be triggered, making it possible to build without Node.js and Internet connectivity.

Parallelism (`make -j <num>`) is not supported.

More info: https://docs.gitea.io/en-us/install-from-source/

## Using

    ./gitea web

NOTE: If you're interested in using our APIs, we have experimental
support with [documentation](https://try.gitea.io/api/swagger).

## Contributing

Expected workflow is: Fork -> Patch -> Push -> Pull Request

NOTES:

1. **YOU MUST READ THE [CONTRIBUTORS GUIDE](CONTRIBUTING.md) BEFORE STARTING TO WORK ON A PULL REQUEST.**
2. If you have found a vulnerability in the project, please write privately to **security@gitea.io**. Thanks!

## Translating

Translations are done through Crowdin. If you want to translate to a new language ask one of the managers in the Crowdin project to add a new language there.

You can also just create an issue for adding a language or ask on discord on the #translation channel. If you need context or find some translation issues, you can leave a comment on the string or ask on Discord. For general translation questions there is a section in the docs. Currently a bit empty but we hope to fill it as questions pop up.

https://docs.gitea.io/en-us/translation-guidelines/

[![Crowdin](https://badges.crowdin.net/gitea/localized.svg)](https://crowdin.com/project/gitea)

## Further information

For more information and instructions about how to install Gitea, please look at our [documentation](https://docs.gitea.io/en-us/).
If you have questions that are not covered by the documentation, you can get in contact with us on our [Discord server](https://discord.gg/Gitea) or create  a post in the [discourse forum](https://discourse.gitea.io/).

We maintain a list of Gitea-related projects at [gitea/awesome-gitea](https://gitea.com/gitea/awesome-gitea).

The Hugo-based documentation theme is hosted at [gitea/theme](https://gitea.com/gitea/theme).

The official Gitea CLI is developed at [gitea/tea](https://gitea.com/gitea/tea).

## Authors

- [Maintainers](https://github.com/orgs/go-gitea/people)
- [Contributors](https://github.com/go-gitea/gitea/graphs/contributors)
- [Translators](options/locale/TRANSLATORS)

## FAQ

**How do you pronounce Gitea?**

Gitea is pronounced [/ɡɪ’ti:/](https://youtu.be/EM71-2uDAoY) as in "gi-tea" with a hard g.

**Why is this not hosted on a Gitea instance?**

We're [working on it](https://github.com/go-gitea/gitea/issues/1029).

## License

This project is licensed under the MIT License.
See the [LICENSE](https://github.com/go-gitea/gitea/blob/main/LICENSE) file
for the full license text.
