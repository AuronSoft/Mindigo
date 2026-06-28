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
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "==> Sinh APP_KEY"
    php artisan key:generate --force
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

# 5) Tạo symlink storage để phục vụ file public
php artisan storage:link 2>/dev/null || true

# 6) Đảm bảo quyền ghi cho storage (kể cả thư mục volume vừa mount)
chown -R www-data:www-data storage bootstrap/cache || true

# 7) Dọn & cache lại cấu hình
php artisan optimize:clear || true

echo "==> Khởi động Apache."
exec "$@"
