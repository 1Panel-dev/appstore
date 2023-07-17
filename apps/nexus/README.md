# Sonatype Nexus3 Docker: sonatype/nexus3

A Dockerfile for Sonatype Nexus Repository Manager 3, starting with 3.18 the image is based on the [Red Hat Universal Base Image](https://www.redhat.com/en/blog/introducing-red-hat-universal-base-image) while earlier versions used CentOS.

* [Contribution Guidlines](#contribution-guidelines)
* [Running](#running)
* [Building the Nexus Repository Manager image](#building-the-nexus-repository-manager-image)
* [Chef Solo for Runtime and Application](#chef-solo-for-runtime-and-application)
* [Testing the Dockerfile](#testing-the-dockerfile)
* [Red Hat Certified Image](#red-hat-certified-image)
* [Notes](#notes)
    * [Persistent Data](#persistent-data)
* [Getting Help](#getting-help)

## Contribution Guidelines

Go read [our contribution guidelines](https://github.com/sonatype/docker-nexus3/blob/main/.github/CONTRIBUTING.md) to get a bit more familiar with how
we would like things to flow.

## Getting Help

Looking to contribute to our Docker image but need some help? There's a few ways to get information or our attention:

* Chat with us on [Gitter](https://gitter.im/sonatype/nexus-developers)
* File an issue [on our public JIRA](https://issues.sonatype.org/projects/NEXUS/)
* Check out the [Nexus3](http://stackoverflow.com/questions/tagged/nexus3) tag on Stack Overflow
* Check out the [Nexus Repository User List](https://groups.google.com/a/glists.sonatype.com/forum/?hl=en#!forum/nexus-users)

## License Disclaimer

_Nexus Repository OSS is distributed with Sencha Ext JS pursuant to a FLOSS Exception agreed upon between Sonatype, Inc. and Sencha Inc. Sencha Ext JS is licensed under GPL v3 and cannot be redistributed as part of a closed source work._
