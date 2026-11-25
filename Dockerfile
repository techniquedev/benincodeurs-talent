# syntax=docker/dockerfile:1
# Talent Board Bénin Codeurs : image de production pour Render (PHP 8.4 FPM + Nginx, pilotés par Supervisor).

# --- 1. Dépendances PHP ---------------------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts

# --- 2. Assets front (Vite + Tailwind) -------------------------------------------------------
FROM node:24-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
COPY app ./app
COPY --from=vendor /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
# Thème admin Filament : il importe les CSS de Filament depuis vendor.
COPY --from=vendor /app/vendor/filament ./vendor/filament
RUN npm run build

# --- 3. Image finale -------------------------------------------------------------------------
FROM php:8.4-fpm-alpine

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN apk add --no-cache nginx supervisor gettext \
    && install-php-extensions pdo_pgsql pdo_sqlite intl zip gd bcmath exif opcache pcntl \
    && rm -f /etc/nginx/http.d/default.conf

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-talent.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-talent.conf
COPY docker/nginx.conf.template /etc/nginx/templates/default.conf.template
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/start.sh /usr/local/bin/start
RUN sed -i 's/\r$//' /usr/local/bin/start && chmod +x /usr/local/bin/start

WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

# Assets Filament (CSS, JS, polices) : ignorés par git, donc à publier dans l'image,
# sinon /admin s'affiche sans style.
RUN php artisan package:discover --ansi \
    && php artisan filament:assets --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PORT=10000

EXPOSE 10000

CMD ["/usr/local/bin/start"]
