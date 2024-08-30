ARG PHP_IMAGE
ARG PHP_EXTENSIONS
FROM ${PHP_IMAGE}

COPY data/install-ext /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-ext

RUN install-ext ${PHP_EXTENSIONS}

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

ENTRYPOINT ["supervisord", "--nodaemon", "--configuration", "/etc/supervisord.conf"]

WORKDIR /www