# syntax=docker/dockerfile:1.7

# Dockerfile cho Mindigo LMS
# Stage 1 build asset Vite. Stage 2 build app Laravel chạy bằng PHP/Apache.

FROM node:20-alpine AS assets
WORKDIR /app

# Cài dependency frontend trước để tận dụng cache khi package-lock không đổi.
COPY package.json package-lock.json* ./
RUN npm ci

# Vite của dự án chỉ dùng asset trong packages, repo không có thư mục resources ở root.
COPY vite.config.js ./
COPY packages ./packages
RUN npm run build


FROM php:8.3-apache AS app
WORKDIR /var/www/html

# Cài thư viện hệ thống và các PHP extension Laravel cần.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
        libonig-dev libxml2-dev default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring bcmath zip gd exif \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini

# Cài dependency PHP trước khi copy toàn bộ source để Docker tận dụng cache.
# Khi chỉ sửa code app, Composer không phải cài lại từ đầu.
# Cache mount giúp Composer không tải lại package trên cùng máy build.
COPY composer.json composer.lock ./
COPY packages ./packages
RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    COMPOSER_PROCESS_TIMEOUT=1200 \
    COMPOSER_MAX_PARALLEL_HTTP=4 \
    composer install --no-dev --no-scripts --optimize-autoloader --no-interaction --no-progress

# Copy toàn bộ mã nguồn ứng dụng.
COPY . .
COPY --from=assets /app/public/build ./public/build

# Tối ưu autoload sau khi source đã có đủ artisan, config, route và package local.
RUN composer dump-autoload --no-dev --optimize --no-interaction

# Cấp quyền ghi cho Laravel storage và cache.
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Chuẩn hóa entrypoint và cấp quyền chạy.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
