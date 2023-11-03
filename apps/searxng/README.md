------------------------------------------------------------------------

<img src="https://raw.githubusercontent.com/searxng/searxng/master/src/brand/searxng.svg" alt="SearXNGlogo" width="15%">

------------------------------------------------------------------------

Privacy-respecting, hackable [meta search engine](https://en.wikipedia.org/wiki/Metasearch_engine)

[Searx.space](https://searx.space) lists ready-to-use running instances.

A [user](https://docs.searxng.org/user),[admin](https://docs.searxng.org/admin) and[developer](https://docs.searxng.org/dev) handbook is available on the[homepage](https://docs.searxng.org/).

[![SearXNG
install](https://img.shields.io/badge/-install-blue)](https://docs.searxng.org/admin/installation.html) [![SearXNG
homepage](https://img.shields.io/badge/-homepage-blue)](https://docs.searxng.org/) [![SearXNGwiki](https://img.shields.io/badge/-wiki-blue)](https://github.com/searxng/searxng/wiki) [![AGPLLicense](https://img.shields.io/badge/license-AGPL-blue.svg)](https://github.com/searxng/searxng/blob/master/LICENSE) [![Issues](https://img.shields.io/github/issues/searxng/searxng?color=yellow&label=issues)](https://github.com/searxng/searxng/issues) [![commits](https://img.shields.io/github/commit-activity/y/searxng/searxng?color=yellow&label=commits)](https://github.com/searxng/searxng/commits/master) [![weblate](https://translate.codeberg.org/widgets/searxng/-/searxng/svg-badge.svg)](https://translate.codeberg.org/projects/searxng/) <a href="https://docs.searxng.org/"><img src="https://raw.githubusercontent.com/searxng/searxng/master/src/brand/searxng-wordmark.svg" alt="SearXNGlogo" width="1.4%"></a>


------------------------------------------------------------------------

# Contact

Ask questions or just chat about SearXNG on

IRC

   [#searxng on libera.chat](https://web.libera.chat/?channel=#searxng) which is bridged to Matrix.

Matrix

   [#searxng:matrix.org](https://matrix.to/#/#searxng:matrix.org)

# Differences to searx

SearXNG is a fork of [searx](https://github.com/searx/searx), with notable changes:

## User experience

-   Reworked (and still simple) theme:
    -   Usable on desktop, tablet and mobile.
    -   Light and dark versions (available in the preferences).
    -   Right-to-left language support.
    -   [Screenshots](https://dev.searxng.org/screenshots.html)
-   The translations are up to date, you can contribute on [Weblate](https://translate.codeberg.org/projects/searxng/searxng/)
-   The preferences page has been updated:
    -   Browse which engines are reliable or not.
    -   Engines are grouped inside each tab.
    -   Each engine has a description.
-   Thanks to the anonymous metrics, it is easier to report malfunctioning engines, so they get fixed quicker
    -   [Turn off metrics on the server](https://docs.searxng.org/admin/engines/settings.html#general)if you don\'t want them recorded.
-   Administrators can [block and/or replace the URLs in the search results](https://github.com/searxng/searxng/blob/5c1c0817c3996c5670a545d05831d234d21e6217/searx/settings.yml#L191-L199)

## Setup

-   No need for [Morty](https://github.com/asciimoo/morty) to proxy images, even on a public instance.
-   No need for [Filtron](https://github.com/searxng/filtron) to block bots, as there is now a built-in [limiter](https://docs.searxng.org/src/searx.plugins.limiter.html).
-   A well maintained [Dockerimage](https://github.com/searxng/searxng-docker), now also built for ARM64 and ARM/v7 architectures. (Alternatively there are up to date installation scripts.)

## Contributing

-   Readable debug log.
-   Contributing is easier, thanks to the [Development Quickstart](https://docs.searxng.org/dev/quickstart.html) guide.
-   A lot of code cleanup and bugfixes.
-   Up to date list dependencies.

# Translations

Help translate SearXNG at [Weblate](https://translate.codeberg.org/projects/searxng/searxng/)

![](https://translate.codeberg.org/widgets/searxng/-/multi-auto.svg)

# Codespaces

You can contribute from your browser using [GitHub Codespaces](https://docs.github.com/en/codespaces/overview):

-   Fork the repository
-   Click on the `<> Code` green button
-   Click on the `Codespaces` tab instead of `Local`
-   Click on `Create codespace on master`
-   VSCode is going to start in the browser
-   Wait for `git pull && make install` to appears and then to disapear
-   You have [120 hours per month](https://github.com/settings/billing)(see also your [list of existing Codespaces](https://github.com/codespaces))
-   You can start SearXNG using `make run` in the terminal or by pressing `Ctrl+Shift+B`.
