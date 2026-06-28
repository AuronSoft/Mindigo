# ============================================================================
#  Mindigo LMS — Dockerfile (multi-stage)
#  Stage 1 (assets): dùng Node build CSS/JS bằng Vite -> public/build
#  Stage 2 (app):    PHP 8.3 + Apache, cài extension, Composer, copy mã nguồn
# ============================================================================

# ----- Stage 1: build front-end assets bằng Vite -----
FROM node:20-alpine AS assets
WORKDIR /app

# Cài dependencies trước (tận dụng cache layer khi package.json không đổi)
COPY package.json package-lock.json* ./
RUN npm install

# Copy phần mã nguồn mà Vite cần (cấu hình + toàn bộ packages chứa css/js)
COPY vite.config.js ./
COPY resources ./resources
COPY packages ./packages

# Xuất ra thư mục public/build (manifest + assets đã nén)
RUN npm run build


# Stage 2: ứng dụng PHP + Apache 
FROM php:8.3-apache AS app

# Thư mục làm việc trong container
WORKDIR /var/www/html

# Cài thư viện hệ thống + các PHP extension Laravel cần
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
        libonig-dev libxml2-dev default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring bcmath zip gd exif \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Lấy Composer từ image chính thức
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Cấu hình Apache (đặt DocumentRoot vào /public) và php.ini tuỳ chỉnh
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/php.ini       /usr/local/etc/php/conf.d/zz-app.ini

# Copy toàn bộ mã nguồn ứng dụng vào container
COPY . .

# Lấy asset đã build ở stage 1 (tránh phải cài Node trong image app)
COPY --from=assets /app/public/build ./public/build

# Cài dependency PHP (production), tối ưu autoload
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Phân quyền cho web server ghi vào storage & cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Script khởi động: chờ DB -> tạo key/.env -> migrate -> chạy Apache
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
