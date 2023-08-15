## Web Site Change Detection, Restock monitoring and notifications.

**_Detect website content changes and perform meaningful actions - trigger notifications via Discord, Email, Slack, Telegram, API calls and many more._**

_Live your data-life pro-actively._ 


[<img src="https://raw.githubusercontent.com/dgtlmoon/changedetection.io/master/docs/screenshot.png" style="max-width:100%;" alt="Self-hosted web page change monitoring"  title="Self-hosted web page change monitoring"  />](https://changedetection.io?src=github)

[![Release Version][release-shield]][release-link] [![Docker Pulls][docker-pulls]][docker-link] [![License][license-shield]](https://github.com/dgtlmoon/changedetection.io/blob/master/LICENSE.md)

![changedetection.io](https://github.com/dgtlmoon/changedetection.io/actions/workflows/test-only.yml/badge.svg?branch=master)

[**Don't have time? Let us host it for you! try our $8.99/month subscription - use our proxies and support!**](https://changedetection.io) , _half the price of other website change monitoring services!_

- Chrome browser included.
- Super fast, no registration needed setup.
- Get started watching and receiving website change notifications straight away.


### Target specific parts of the webpage using the Visual Selector tool.

Available when connected to a <a href="https://github.com/dgtlmoon/changedetection.io/wiki/Playwright-content-fetcher">playwright content fetcher</a> (included as part of our subscription service)

[<img src="https://raw.githubusercontent.com/dgtlmoon/changedetection.io/master/docs/visualselector-anim.gif" style="max-width:100%;" alt="Self-hosted web page change monitoring context difference "  title="Self-hosted web page change monitoring context difference " />](https://changedetection.io?src=github)

### Easily see what changed, examine by word, line, or individual character.

[<img src="https://raw.githubusercontent.com/dgtlmoon/changedetection.io/master/docs/screenshot-diff.png" style="max-width:100%;" alt="Self-hosted web page change monitoring context difference "  title="Self-hosted web page change monitoring context difference " />](https://changedetection.io?src=github)


### Perform interactive browser steps

Fill in text boxes, click buttons and more, setup your changedetection scenario. 

Using the **Browser Steps** configuration, add basic steps before performing change detection, such as logging into websites, adding a product to a cart, accept cookie logins, entering dates and refining searches.

[<img src="docs/browsersteps-anim.gif" style="max-width:100%;" alt="Self-hosted web page change monitoring context difference "  title="Website change detection with interactive browser steps, login, cookies etc" />](https://changedetection.io?src=github)

After **Browser Steps** have been run, then visit the **Visual Selector** tab to refine the content you're interested in.
Requires Playwright to be enabled.


### Example use cases

- Products and services have a change in pricing
- _Out of stock notification_ and _Back In stock notification_
- Monitor and track PDF file changes, know when a PDF file has text changes.
- Governmental department updates (changes are often only on their websites)
- New software releases, security advisories when you're not on their mailing list.
- Festivals with changes
- Discogs restock alerts and monitoring
- Realestate listing changes
- Know when your favourite whiskey is on sale, or other special deals are announced before anyone else
- COVID related news from government websites
- University/organisation news from their website
- Detect and monitor changes in JSON API responses 
- JSON API monitoring and alerting
- Changes in legal and other documents
- Trigger API calls via notifications when text appears on a website
- Glue together APIs using the JSON filter and JSON notifications
- Create RSS feeds based on changes in web content
- Monitor HTML source code for unexpected changes, strengthen your PCI compliance
- You have a very sensitive list of URLs to watch and you do _not_ want to use the paid alternatives. (Remember, _you_ are the product)
- Get notified when certain keywords appear in Twitter search results
- Proactively search for jobs, get notified when companies update their careers page, search job portals for keywords.
- Get alerts when new job positions are open on Bamboo HR and other job platforms
- Website defacement monitoring
- Pokémon Card Restock Tracker / Pokémon TCG Tracker

_Need an actual Chrome runner with Javascript support? We support fetching via WebDriver and Playwright!</a>_

#### Key Features

- Lots of trigger filters, such as "Trigger on text", "Remove text by selector", "Ignore text", "Extract text", also using regular-expressions!
- Target elements with xPath and CSS Selectors, Easily monitor complex JSON with JSONPath or jq
- Switch between fast non-JS and Chrome JS based "fetchers"
- Track changes in PDF files (Monitor text changed in the PDF, Also monitor PDF filesize and checksums)
- Easily specify how often a site should be checked
- Execute JS before extracting text (Good for logging in, see examples in the UI!)
- Override Request Headers, Specify `POST` or `GET` and other methods
- Use the "Visual Selector" to help target specific elements
- Configurable [proxy per watch](https://github.com/dgtlmoon/changedetection.io/wiki/Proxy-configuration)
- Send a screenshot with the notification when a change is detected in the web page

We [recommend and use Bright Data](https://brightdata.grsm.io/n0r16zf7eivq) global proxy services, Bright Data will match any first deposit up to $100 using our signup link.

Please :star: star :star: this project and help it grow! https://github.com/dgtlmoon/changedetection.io/

## Installation

### Docker

With Docker composer, just clone this repository and..

```bash
$ docker-compose up -d
```

Docker standalone
```bash
$ docker run -d --restart always -p "127.0.0.1:5000:5000" -v datastore-volume:/datastore --name changedetection.io dgtlmoon/changedetection.io
```

`:latest` tag is our latest stable release, `:dev` tag is our bleeding edge `master` branch.

Alternative docker repository over at ghcr - [ghcr.io/dgtlmoon/changedetection.io](https://ghcr.io/dgtlmoon/changedetection.io)

### Windows

See the install instructions at the wiki https://github.com/dgtlmoon/changedetection.io/wiki/Microsoft-Windows

### Python Pip

Check out our pypi page https://pypi.org/project/changedetection.io/

```bash
$ pip3 install changedetection.io
$ changedetection.io -d /path/to/empty/data/dir -p 5000
```

Then visit http://127.0.0.1:5000 , You should now be able to access the UI.

_Now with per-site configurable support for using a fast built in HTTP fetcher or use a Chrome based fetcher for monitoring of JavaScript websites!_

## Updating changedetection.io

### Docker
```
docker pull dgtlmoon/changedetection.io
docker kill $(docker ps -a -f name=changedetection.io -q)
docker rm $(docker ps -a -f name=changedetection.io -q)
docker run -d --restart always -p "127.0.0.1:5000:5000" -v datastore-volume:/datastore --name changedetection.io dgtlmoon/changedetection.io
```

### docker-compose

```bash
docker-compose pull && docker-compose up -d
```

See the wiki for more information https://github.com/dgtlmoon/changedetection.io/wiki


## Filters

XPath, JSONPath, jq, and CSS support comes baked in! You can be as specific as you need, use XPath exported from various XPath element query creation tools. 
(We support LXML `re:test`, `re:match` and `re:replace`.)

## Notifications

ChangeDetection.io supports a massive amount of notifications (including email, office365, custom APIs, etc) when a web-page has a change detected thanks to the <a href="https://github.com/caronc/apprise">apprise</a> library.
Simply set one or more notification URL's in the _[edit]_ tab of that watch.

Just some examples

    discord://webhook_id/webhook_token
    flock://app_token/g:channel_id
    gitter://token/room
    gchat://workspace/key/token
    msteams://TokenA/TokenB/TokenC/
    o365://TenantID:AccountEmail/ClientID/ClientSecret/TargetEmail
    rocket://user:password@hostname/#Channel
    mailto://user:pass@example.com?to=receivingAddress@example.com
    json://someserver.com/custom-api
    syslog://
 
<a href="https://github.com/caronc/apprise#popular-notification-services">And everything else in this list!</a>

<img src="https://raw.githubusercontent.com/dgtlmoon/changedetection.io/master/docs/screenshot-notifications.png" style="max-width:100%;" alt="Self-hosted web page change monitoring notifications"  title="Self-hosted web page change monitoring notifications"  />

Now you can also customise your notification content and use <a target="_new" href="https://jinja.palletsprojects.com/en/3.0.x/templates/">Jinja2 templating</a> for their title and body!

## JSON API Monitoring

Detect changes and monitor data in JSON API's by using either JSONPath or jq to filter, parse, and restructure JSON as needed.

![image](https://raw.githubusercontent.com/dgtlmoon/changedetection.io/master/docs/json-filter-field-example.png)

This will re-parse the JSON and apply formatting to the text, making it super easy to monitor and detect changes in JSON API results

![image](https://raw.githubusercontent.com/dgtlmoon/changedetection.io/master/docs/json-diff-example.png)

### JSONPath or jq?

For more complex parsing, filtering, and modifying of JSON data, jq is recommended due to the built-in operators and functions. Refer to the [documentation](https://stedolan.github.io/jq/manual/) for more specifc information on jq.

One big advantage of `jq` is that you can use logic in your JSON filter, such as filters to only show items that have a value greater than/less than etc.

See the wiki https://github.com/dgtlmoon/changedetection.io/wiki/JSON-Selector-Filter-help for more information and examples

### Parse JSON embedded in HTML!

When you enable a `json:` or `jq:` filter, you can even automatically extract and parse embedded JSON inside a HTML page! Amazingly handy for sites that build content based on JSON, such as many e-commerce websites. 

```
<html>
...
<script type="application/ld+json">

{
   "@context":"http://schema.org/",
   "@type":"Product",
   "offers":{
      "@type":"Offer",
      "availability":"http://schema.org/InStock",
      "price":"3949.99",
      "priceCurrency":"USD",
      "url":"https://www.newegg.com/p/3D5-000D-001T1"
   },
   "description":"Cobratype King Cobra Hero Desktop Gaming PC",
   "name":"Cobratype King Cobra Hero Desktop Gaming PC",
   "sku":"3D5-000D-001T1",
   "itemCondition":"NewCondition"
}
</script>
```  

`json:$..price` or `jq:..price` would give `3949.99`, or you can extract the whole structure (use a JSONpath test website to validate with)

The application also supports notifying you that it can follow this information automatically


## Proxy Configuration

See the wiki https://github.com/dgtlmoon/changedetection.io/wiki/Proxy-configuration , we also support using [BrightData proxy services where possible]( https://github.com/dgtlmoon/changedetection.io/wiki/Proxy-configuration#brightdata-proxy-support)

## Raspberry Pi support?

Raspberry Pi and linux/arm/v6 linux/arm/v7 arm64 devices are supported! See the wiki for [details](https://github.com/dgtlmoon/changedetection.io/wiki/Fetching-pages-with-WebDriver)

## API Support

Supports managing the website watch list [via our API](https://changedetection.io/docs/api_v1/index.html)

## Support us

Do you use changedetection.io to make money? does it save you time or money? Does it make your life easier? less stressful? Remember, we write this software when we should be doing actual paid work, we have to buy food and pay rent just like you.


Firstly, consider taking out a [change detection monthly subscription - unlimited checks and watches](https://changedetection.io?src=github) , even if you don't use it, you still get the warm fuzzy feeling of helping out the project. (And who knows, you might just use it!)

Or directly donate an amount PayPal [![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/donate/?hosted_button_id=7CP6HR9ZCNDYJ)

Or BTC `1PLFN327GyUarpJd7nVe7Reqg9qHx5frNn`

<img src="https://raw.githubusercontent.com/dgtlmoon/changedetection.io/master/docs/btc-support.png" style="max-width:50%;" alt="Support us!"  />

## Commercial Support

I offer commercial support, this software is depended on by network security, aerospace , data-science and data-journalist professionals just to name a few, please reach out at dgtlmoon@gmail.com for any enquiries, I am more than glad to work with your organisation to further the possibilities of what can be done with changedetection.io


[release-shield]: https://img.shields.io:/github/v/release/dgtlmoon/changedetection.io?style=for-the-badge
[docker-pulls]: https://img.shields.io/docker/pulls/dgtlmoon/changedetection.io?style=for-the-badge
[test-shield]: https://github.com/dgtlmoon/changedetection.io/actions/workflows/test-only.yml/badge.svg?branch=master

[license-shield]: https://img.shields.io/github/license/dgtlmoon/changedetection.io.svg?style=for-the-badge
[release-link]: https://github.com/dgtlmoon/changedetection.io/releases
[docker-link]: https://hub.docker.com/r/dgtlmoon/changedetection.io