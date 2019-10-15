ARG VERSION

# Dev image
FROM php:${VERSION}-fpm-alpine AS dev

## Install system dependencies
RUN apk update && \
    apk add --no-cache --virtual dev-deps git autoconf gcc g++ make && \
    apk add --no-cache zlib-dev libzip-dev libpq icu-dev postgresql-dev libpng-dev && \
    apk add --no-cache libjpeg-turbo-dev freetype-dev imagemagick-dev libxslt-dev

# Install modules
RUN docker-php-ext-install \
        opcache \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        sockets \
        xsl \
        intl
CMD ["php-fpm"]

## Copy php default configuration
COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/default.ini

ENV APP_ENV=dev
WORKDIR /code

## Install composer
RUN wget https://getcomposer.org/installer && \
    php installer --install-dir=/usr/local/bin/ --filename=composer && \
    rm installer && \
    composer global require hirak/prestissimo

# Test image
FROM dev AS test

WORKDIR /code

## Copy project files to workdir
COPY . .

## Install application dependencies
RUN composer install --no-interaction --optimize-autoloader

## Change files owner to php-fpm default user
RUN chown -R www-data:www-data .

# Prod image
FROM test AS prod

ENV APP_ENV=prod
WORKDIR /code

## Remove dev dependencies
RUN composer install --no-dev --no-interaction --optimize-autoloader

## Cleanup
RUN apk del dev-deps && \
    composer global remove hirak/prestissimo && \
    rm /usr/local/bin/composer
