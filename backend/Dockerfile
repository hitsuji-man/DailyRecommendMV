# backend/Dockerfile

FROM composer:2 AS vendor
WORKDIR /app

ENV COMPOSER_MEMORY_LIMIT=-1

RUN apk add --no-cache \
    postgresql-dev \
    oniguruma-dev \
    libzip-dev \
 && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    zip

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

FROM node:20-alpine AS assets
WORKDIR /app

COPY package.json package-lock.json* ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi

COPY resources ./resources
COPY public ./public
COPY vite.config.* ./
COPY tailwind.config.* ./
COPY postcss.config.* ./
RUN npm run build

FROM php:8.2-cli-alpine

WORKDIR /var/www/html

# Laravel + PostgreSQL(Neon) で必要になりやすい拡張
RUN apk add --no-cache \
        bash \
        curl \
        git \
        unzip \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        mbstring \
        zip

# アプリ本体をコピー
COPY . .

# Composer vendor をコピー
COPY --from=vendor /app/vendor ./vendor

# Vite build成果物をコピー
COPY --from=assets /app/public/build ./public/build

# Laravel 用の最低限ディレクトリ作成
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 10000

CMD sh -c "php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"
