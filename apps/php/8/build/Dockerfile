ARG PHP_IMAGE
FROM ${PHP_IMAGE}

ARG PHP_EXTENSIONS
ARG CONTAINER_PACKAGE_URL

RUN sed -i "s|URIs: http://deb.debian.org/debian|URIs: ${CONTAINER_PACKAGE_URL}/debian|g" /etc/apt/sources.list.d/debian.sources && \
    apt-get update

COPY ./data /tmp/data

COPY data/install-ext /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-ext

ENV PHP_INI_SCAN_DIR=/usr/local/etc/php/conf.d

RUN pear config-set php_ini /usr/local/etc/php/php.ini

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

ENTRYPOINT ["supervisord", "--nodaemon", "--configuration", "/etc/supervisord.conf"]

WORKDIR /www