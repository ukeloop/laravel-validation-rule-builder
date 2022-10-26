FROM php:8.0-fpm-alpine

WORKDIR /workspace

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apk add bash
RUN apk add --update --no-cache zip libzip-dev
RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip

RUN composer config -g process-timeout 3600 && \
  composer config -g repos.packagist composer https://packagist.org
