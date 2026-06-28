# 1. Hướng dẫn cài đặt Mindigo LMS trên máy local

Tài liệu này hướng dẫn cài đặt **Mindigo LMS** ở môi trường phát triển local. Hệ thống
dùng **Laravel 12**, **PHP 8.2+**, **MySQL/MariaDB**, **Vite** và **Tailwind CSS v4**.
Nếu muốn chạy bằng Docker, xem tiếp file `docs/5-docker-guide.md`.

---

## 1. Yêu cầu môi trường

| Thành phần | Phiên bản khuyến nghị | Vai trò |
|------------|------------------------|---------|
| PHP | 8.2 trở lên | Chạy Laravel và các package nội bộ. |
| Composer | 2.x | Cài thư viện PHP. |
| Node.js | 20.x trở lên | Build giao diện qua Vite/Tailwind. |
| NPM | 10.x trở lên | Cài package frontend. |
| MySQL/MariaDB | MySQL 8 hoặc MariaDB tương đương | Lưu dữ liệu hệ thống LMS. |
| Laragon/XAMPP | Tuỳ chọn | Tạo nhanh môi trường Windows local. |

Các extension PHP thường cần có:

```bash
pdo_mysql mbstring bcmath zip gd exif fileinfo openssl tokenizer xml curl
```

---

## 2. Lấy mã nguồn và cài thư viện

```bash
# 1) Clone dự án
git clone <repo-url> Mindigo
cd Mindigo

# 2) Cài thư viện PHP
composer install

# 3) Cài thư viện frontend
npm install
```

> Dự án dùng nhiều package nội bộ trong thư mục `packages/`. Composer sẽ map các package
> này qua cấu hình path repository trong `composer.json`, vì vậy cần chạy lệnh ở đúng thư
> mục gốc của dự án.

---

## 3. Cấu hình file `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Cấu hình database local, ví dụ:

```env
APP_NAME="Mindigo LMS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mindigo
DB_USERNAME=root
DB_PASSWORD=
```

Tạo database rỗng trước khi migrate:

```sql
CREATE DATABASE mindigo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 4. Khởi tạo dữ liệu

```bash
# Tạo bảng
php artisan migrate

# Tạo dữ liệu mẫu
php artisan db:seed

# Tạo symlink cho file upload
php artisan storage:link
```

Tài khoản mẫu sau khi seed:

| Vai trò | Email | Mật khẩu |
|---------|-------|----------|
| Quản trị | `admin@mindigo.com` | `123456` |
| Giáo viên | `teacher@mindigo.com` | `123456` |
| Học sinh | `student@mindigo.com` | `123456` |

> Nếu cần reset sạch dữ liệu demo: `php artisan migrate:fresh --seed`. Lệnh này xoá toàn
> bộ bảng hiện có nên chỉ dùng ở môi trường local/demo.

---

## 5. Chạy ứng dụng

Cách chạy đầy đủ trong lúc phát triển:

```bash
composer run dev
```

Script này chạy đồng thời:

- Laravel development server.
- Queue listener.
- Log viewer (`pail`).
- Vite dev server.

Nếu muốn chạy riêng từng phần:

```bash
php artisan serve
npm run dev
php artisan queue:listen
```

Mở trình duyệt:

```text
http://127.0.0.1:8000
```

Build tài nguyên giao diện để chuẩn bị deploy:

```bash
npm run build
```

---

## 6. Các lệnh Artisan hay dùng

| Mục đích | Lệnh |
|----------|------|
| Xem danh sách route | `php artisan route:list` |
| Xem route giáo viên | `php artisan route:list --path=teacher` |
| Xoá cache Laravel | `php artisan optimize:clear` |
| Tạo lại autoload Composer | `composer dump-autoload` |
| Chạy migration mới | `php artisan migrate` |
| Rollback migration gần nhất | `php artisan migrate:rollback` |
| Seed lại dữ liệu | `php artisan db:seed` |
| Reset DB local | `php artisan migrate:fresh --seed` |

---

## 7. Cấu trúc thư mục quan trọng

| Thư mục/file | Vai trò |
|--------------|---------|
| `app/` | Code Laravel mặc định của ứng dụng gốc. |
| `packages/Mindigo/` | Các module lõi: Auth, Dashboard, Question Bank, Report, Setting... |
| `packages/Teacher/` | Các module dành cho giáo viên. |
| `packages/Students/` | Các module dành cho học sinh. |
| `database/migrations/` | Migration chung của hệ thống. |
| `database/seeders/` | Dữ liệu mẫu cho demo/test. |
| `resources/` | Tài nguyên giao diện chung. |
| `public/` | Web root của Laravel, chứa `index.php` và asset build. |
| `storage/` | Log, cache, file upload. |

---

## 8. Lỗi thường gặp khi cài local

### 8.1. Không kết nối được database

Kiểm tra lại `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` trong `.env`,
sau đó chạy:

```bash
php artisan optimize:clear
php artisan migrate
```

### 8.2. Ảnh/file upload không xem được

Chạy:

```bash
php artisan storage:link
```

Đồng thời kiểm tra quyền ghi của thư mục `storage/`.

### 8.3. Giao diện không cập nhật

```bash
npm run dev
php artisan optimize:clear
```

Nếu chạy bản build:

```bash
npm run build
```

### 8.4. Class trong package không load

```bash
composer dump-autoload
php artisan optimize:clear
```

Kiểm tra `composer.json` của package và service provider đã đăng ký đúng chưa.

---

## 9. Nội dung phục vụ thi vấn đáp

- **Laravel** là framework PHP theo mô hình MVC, giúp tổ chức route, controller, model,
  view, migration và validation rõ ràng.
- **Composer** quản lý thư viện PHP và autoload class theo chuẩn PSR-4.
- **NPM/Vite** quản lý và build tài nguyên frontend.
- **Migration** giúp quản lý cấu trúc database bằng code, dễ đồng bộ giữa nhiều máy.
- **Seeder** tạo dữ liệu mẫu để demo, test và kiểm thử luồng nghiệp vụ.
- **Storage link** cho phép file trong `storage/app/public` được truy cập qua `public/storage`.

---

*Cập nhật cho Mindigo LMS — Laravel 12, PHP 8.2+, MySQL, Vite/Tailwind v4.*
