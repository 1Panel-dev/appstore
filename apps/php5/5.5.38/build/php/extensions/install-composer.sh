#!/bin/sh

# The latest mirror's composer version only support for PHP 7.2.5
# And if your PHP version is lesser than that, will be download supported version.
supportLatest=$(php -r "echo version_compare(PHP_VERSION, '7.2.5', '>');")

if [ "$supportLatest" -eq "1" ]; then
    curl -o /usr/bin/composer https://mirrors.aliyun.com/composer/composer.phar \
    && chmod +x /usr/bin/composer
else
    curl  -ksS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer --disable-tls \
    && chmod +x /usr/bin/composer \
    && rm -rf /tmp/composer-setup.php
fi