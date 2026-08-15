#!/bin/bash
set -e

cd /var/www/html

echo "==> Mindigo LMS container starting..."

# 1) Đảm bảo có file .env (image không copy .env, tạo từ .env.example)
if [ ! -f .env ]; then
    echo "==> Tạo .env từ .env.example"
    cp .env.example .env
fi

# 2) Sinh APP_KEY nếu chưa có khoá hợp lệ
if [[ "${APP_KEY:-}" != base64:* ]] && ! grep -q "^APP_KEY=base64:" .env; then
    echo "==> Sinh APP_KEY"
    php artisan key:generate --force
fi

# Queue, scheduler and Reverb share the immutable app image. Only the PHP-FPM
# app container owns migrations and public asset bootstrap.
if [ "${APP_BOOTSTRAP:-false}" != "true" ]; then
    exec "$@"
fi

# 3) Chờ MySQL sẵn sàng (dùng thông tin từ biến môi trường)
echo "==> Chờ cơ sở dữ liệu (${DB_HOST}:${DB_PORT})..."
until php -r '
    try {
        new PDO(
            "mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: "3306"),
            getenv("DB_USERNAME"),
            getenv("DB_PASSWORD")
        );
        exit(0);
    } catch (Throwable $e) { exit(1); }
' 2>/dev/null; do
    echo "   ... DB chưa sẵn sàng, thử lại sau 3s"
    sleep 3
done
echo "==> DB đã sẵn sàng."

# 4) Chạy migration (và seed lần đầu nếu bảng users chưa có dữ liệu)
echo "==> Chạy migrate"
php artisan migrate --force

# An isolated development database starts empty. Seed it once so local login
# accounts exist, while keeping every later container start idempotent.
if [ "${APP_SEED_DATABASE:-false}" = "true" ]; then
    if php -r '
        $pdo = new PDO(
            "mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: "3306").";dbname=".getenv("DB_DATABASE"),
            getenv("DB_USERNAME"),
            getenv("DB_PASSWORD")
        );
        exit((int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() === 0 ? 0 : 1);
    '; then
        echo "==> Seed fresh development database"
        php artisan db:seed --force
    fi
fi

# 5) Tạo symlink storage để phục vụ file public
php artisan storage:link 2>/dev/null || true

# 5b) Đồng bộ public/ sang volume dùng chung "web_public" để nginx phục vụ static.
#     Volume được mount tại /shared (xem docker-compose). File upload trong
#     storage/app/public được copy thành thư mục thật (vì nginx không có mã nguồn).
SHARED_PUBLIC="${SHARED_PUBLIC_DIR:-/shared/public}"
if [ -d "$(dirname "$SHARED_PUBLIC")" ]; then
    echo "==> Đồng bộ public/ sang volume nginx..."
    mkdir -p "$SHARED_PUBLIC"
    # public/storage in the image is a symlink, while the shared volume may
    # contain a directory from an older bootstrap. Remove only this published
    # path before copying; uploaded files live in their separate Docker volume.
    rm -rf "$SHARED_PUBLIC/storage"
    cp -rT /var/www/html/public "$SHARED_PUBLIC"
    # Đảm bảo storage (file upload) là thư mục thật cho nginx, không phải symlink
    if [ "${SYNC_PUBLIC_STORAGE:-true}" = "true" ]; then
        rm -rf "$SHARED_PUBLIC/storage"
        mkdir -p "$SHARED_PUBLIC/storage"
        cp -rT /var/www/html/storage/app/public "$SHARED_PUBLIC/storage"
    else
        rm -f "$SHARED_PUBLIC/storage" 2>/dev/null || true
    fi
    chown -R www-data:www-data "$SHARED_PUBLIC" || true
fi

# 6) Đảm bảo quyền ghi cho storage (kể cả thư mục volume vừa mount)
chown -R www-data:www-data storage bootstrap/cache || true

# 7) Dọn & cache lại cấu hình
php artisan optimize:clear || true

echo "==> Khởi động PHP-FPM."
exec "$@"
