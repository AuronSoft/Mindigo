# syntax=docker/dockerfile:1.7

# Dockerfile.app — Mindigo LMS (PHP-FPM)
# Chứa toàn bộ mã nguồn + assets đã build. Nginx (Dockerfile.nginx) đọc public qua volume dùng chung.

# Stage 1: build asset Vite
FROM node:20-alpine AS assets
WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

# Vite build chỉ đọc assets trong packages, repo không có thư mục resources ở root.
COPY vite.config.js ./
COPY packages ./packages
RUN npm run build

# Stage 2: PHP-FPM
FROM php:8.3-fpm AS app
WORKDIR /var/www/html

# Cài thư viện hệ thống và các PHP extension Laravel cần.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
        libonig-dev libxml2-dev default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring bcmath zip gd exif \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf

# Cài dependency PHP trước khi copy toàn bộ source để Docker tận dụng cache.
COPY composer.json composer.lock ./
COPY packages ./packages
RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    COMPOSER_PROCESS_TIMEOUT=1200 \
    COMPOSER_MAX_PARALLEL_HTTP=4 \
    composer install --no-dev --no-scripts --optimize-autoloader --no-interaction --no-progress

COPY . .
COPY --from=assets /app/public/build ./public/build

# Tối ưu autoload sau khi đã có artisan, config, route và package local.
RUN composer dump-autoload --no-dev --optimize --no-interaction

# Cấp quyền ghi cho Laravel storage và cache.
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Chuẩn hoá entrypoint và cấp quyền chạy.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
