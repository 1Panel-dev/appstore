ARG PHP_IMAGE
FROM ${PHP_IMAGE}

ARG PHP_BASE_VERSION
ARG TZ=Asia/Shanghai
ARG PHP_EXTENSIONS
ARG CONTAINER_PACKAGE_URL

COPY configure-source install-extensions /tmp/v1-php/
RUN set -eux; \
    php -r 'exit(PHP_VERSION === $argv[1] ? 0 : 1);' "$PHP_BASE_VERSION"; \
    sh /tmp/v1-php/configure-source; \
    apt-get update; \
    apt-get install -y --no-install-recommends tzdata; \
    ln -snf "/usr/share/zoneinfo/$TZ" /etc/localtime; \
    printf '%s\n' "$TZ" > /etc/timezone; \
    sh /tmp/v1-php/install-extensions; \
    rm -rf /tmp/v1-php /var/lib/apt/lists/*

COPY php.ini /usr/local/etc/php/php.ini
COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
RUN mkdir -p /var/log/php && test "$(id -u www-data)" = 1000 && php-fpm -t

ENV COMPOSER_HOME=/tmp/composer

# v1 starts FPM directly and mounts its pool config at php-fpm.d/www.conf.
ENTRYPOINT []
CMD ["php-fpm", "-F"]
WORKDIR /www
