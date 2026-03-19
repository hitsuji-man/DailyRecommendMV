# ---------- base ----------
FROM php:8.2-cli-alpine AS base

WORKDIR /var/www/html

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
    zip \
    bcmath

# ---------- vendor ----------
FROM base AS vendor

COPY composer.json composer.lock ./

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php \
 && php composer.phar install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# ---------- final ----------
FROM base

# アプリ本体をコピー
COPY . .

# Composer vendorをコピー
COPY --from=vendor /var/www/html/vendor ./vendor

# Laravel用ディレクトリ
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 10000

CMD sh -c "php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"
