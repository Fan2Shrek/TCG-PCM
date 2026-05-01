# =========================
# BASE
# =========================
FROM php:8.5.2-fpm-alpine AS base

RUN apk add --no-cache \
    nginx \
    git \
    icu-dev \
    oniguruma-dev \
    libzip-dev \
    bash \
    multirun

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    pdo_mysql \
    intl \
    opcache \
    redis \
    zip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

COPY ./nginx.conf /etc/nginx/http.d/default.conf

RUN addgroup -g 1000 app && adduser -G app -g app -s /bin/sh -D app


# =========================
# DEV
# =========================
FROM base AS dev

RUN install-php-extensions \
    pcov

COPY . /app

RUN composer install

CMD ["multirun", "php-fpm -R -F", "nginx -g 'daemon off;'"]

# =========================
# PROD
# =========================
FROM base AS prod

ENV APP_ENV=prod
ENV APP_DEBUG=0

RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

COPY composer.json composer.lock /app/

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts \
    --prefer-dist

COPY . /app

RUN php bin/console cache:clear \
 && php bin/console cache:warmup

CMD ["multirun", "php-fpm -R -F", "nginx -g 'daemon off;'"]
