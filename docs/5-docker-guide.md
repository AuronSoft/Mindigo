# 5. Hướng dẫn triển khai Mindigo LMS bằng Docker

Tài liệu này hướng dẫn đóng gói và chạy toàn bộ hệ thống **Mindigo LMS** (Laravel 12 +
MySQL) bằng Docker, đồng thời giải thích nội dung từng file cấu hình. Phần cuối tổng hợp
kiến thức phục vụ thi vấn đáp học phần *Quy trình và công cụ phát triển phần mềm*.

---

## 1. Bộ file Docker gồm những gì?

| File | Vai trò |
|------|---------|
| `Dockerfile` | Công thức **build image** cho container web (PHP 8.3 + Apache). |
| `docker-compose.yml` | Khai báo & chạy đồng thời 2 container: `app` (web) và `db` (MySQL). |
| `docker/apache/vhost.conf` | Cấu hình Apache, trỏ DocumentRoot vào thư mục `public/` của Laravel. |
| `docker/php/php.ini` | Tinh chỉnh PHP (giới hạn upload, bộ nhớ, múi giờ). |
| `docker/entrypoint.sh` | Script chạy khi container khởi động: tạo `.env`, sinh key, chờ DB, migrate. |
| `.dockerignore` | Loại bỏ file không cần khi build (giảm dung lượng image). |
| `Makefile` | Lối tắt các lệnh Docker hay dùng (`make up`, `make logs`, `make seed`...). |

> Mô hình: **3 container** chạy trên cùng một mạng nội bộ `mindigo`. Container `app`
> kết nối tới `db` bằng **tên service** (`DB_HOST=db`) — Docker tự phân giải DNS nội bộ.
> `phpmyadmin` là tiện ích xem/sửa dữ liệu qua trình duyệt khi demo.

```
┌─────────────────────────────┐        ┌────────────────────────────┐
│  app (mindigo_app)          │        │  db (mindigo_db)           │
│  PHP 8.3 + Apache           │  mạng  │  MySQL 8.0                 │
│  cổng 80  → host 8080       │◀──────▶│  cổng 3306 → host 3307     │
│  /var/www/html (mã nguồn)   │ mindigo│  volume: db_data           │
└─────────────────────────────┘   ▲    └────────────────────────────┘
        ▲ http://localhost:8080    │  ┌────────────────────────────┐
                                   └─▶│  phpmyadmin (mindigo_pma)  │
                                      │  cổng 80 → host 8081       │
                                      └────────────────────────────┘
                                              ▲ http://localhost:8081
```

---

## 2. Yêu cầu trên máy chủ Linux

Cài **Docker Engine** + **Docker Compose plugin** (ví dụ trên Ubuntu):

```bash
# Cài Docker theo script chính thức
curl -fsSL https://get.docker.com | sh

# Cho phép user hiện tại chạy docker không cần sudo (đăng nhập lại sau lệnh này)
sudo usermod -aG docker $USER

# Kiểm tra
docker --version
docker compose version
```

---

## 3. Giải thích nội dung từng file

### 3.1. `Dockerfile` — build image web (multi-stage)

Dùng **2 giai đoạn (multi-stage)** để image cuối gọn nhẹ:

- **Stage `assets` (node:20-alpine):** cài npm, chạy `npm run build` (Vite + Tailwind v4)
  để biên dịch CSS/JS ra `public/build`. Giai đoạn này **chỉ để build**, không nằm trong
  image cuối → không kéo theo Node.js cồng kềnh.
- **Stage `app` (php:8.3-apache):**
  - Cài extension PHP mà Laravel cần: `pdo_mysql, mbstring, bcmath, zip, gd, exif`.
  - Bật `mod_rewrite` của Apache (cho URL đẹp của Laravel).
  - Lấy **Composer** từ image `composer:2`.
  - Copy mã nguồn + copy `public/build` từ stage 1 sang.
  - `composer install --no-dev --optimize-autoloader` để cài thư viện PHP.
  - Cấp quyền ghi cho `storage/` và `bootstrap/cache/`.
  - Đặt `entrypoint.sh` làm lệnh khởi động.

> **Vì sao trỏ về `public/`?** Laravel chỉ để lộ thư mục `public` ra ngoài (chứa `index.php`),
> còn mã nguồn nằm ngoài web root để bảo mật. Vì vậy `vhost.conf` đặt `DocumentRoot`
> vào `/var/www/html/public`.

### 3.2. `docker-compose.yml` — chạy nhiều container

- **service `app`**: build từ `Dockerfile`, ánh xạ cổng `8080:80`, truyền biến môi trường
  DB trỏ tới container `db`, `depends_on` đợi `db` khoẻ (healthcheck) rồi mới chạy, gắn
  volume `storage_data` để giữ file upload.
- **service `db`**: dùng image `mysql:8.0`, đặt tên database/user/mật khẩu qua biến môi
  trường, ánh xạ cổng `3307:3306`, gắn volume `db_data` để **dữ liệu không mất** khi xoá
  container, có `healthcheck` bằng `mysqladmin ping`.
- **`volumes`**: `db_data` (dữ liệu MySQL) và `storage_data` (file người dùng upload).
- **`networks`**: mạng `mindigo` để 2 container thấy nhau qua tên service.

### 3.3. `docker/entrypoint.sh` — việc chạy mỗi lần container start

1. Nếu chưa có `.env` → tạo từ `.env.example`.
2. Nếu chưa có `APP_KEY` hợp lệ → `php artisan key:generate`.
3. **Chờ MySQL sẵn sàng** (vòng lặp thử kết nối PDO) — tránh lỗi khi DB khởi động chậm hơn web.
4. `php artisan migrate --force` — tạo bảng.
5. `php artisan storage:link` — tạo symlink file public.
6. Cấp lại quyền ghi `storage/`, dọn cache, rồi `exec apache2-foreground` (chạy Apache ở foreground).

### 3.4. `.dockerignore`

Loại `node_modules`, `vendor`, `.git`, `.env`, log… khỏi ngữ cảnh build để build nhanh,
image nhẹ và không lộ thông tin nhạy cảm.

---

## 4. Các bước triển khai

```bash
# 1) Lấy mã nguồn về máy chủ (qua Git)
git clone <repo-url> mindigo && cd mindigo

# 2) Build image và chạy nền (lần đầu sẽ lâu vì phải tải thư viện)
docker compose up -d --build

# 3) Theo dõi log quá trình khởi động (migrate, chờ DB...)
docker compose logs -f app

# 4) (Tuỳ chọn) Tạo dữ liệu mẫu: admin/teacher/student + lớp học
docker compose exec app php artisan db:seed

# 5) Mở trình duyệt
#    http://localhost:8080   → ứng dụng Mindigo LMS
#    http://localhost:8081   → phpMyAdmin (đăng nhập: mindigo / secret)
#    Tài khoản mẫu (nếu đã seed): admin@mindigo.com / teacher@mindigo.com / student@mindigo.com  (mật khẩu: 123456)
```

> **Dùng Makefile cho nhanh** (server có `make`): `make up` (build+chạy), `make logs`,
> `make seed`, `make shell`, `make db`, `make down`... Gõ `make help` để xem tất cả.

Dừng / khởi động lại:

```bash
docker compose stop      # dừng container (giữ nguyên)
docker compose start     # chạy lại
docker compose down      # dừng + xoá container & network (VẪN GIỮ volume dữ liệu)
docker compose down -v   # xoá luôn cả volume (MẤT dữ liệu DB) — dùng khi muốn reset sạch
```

---

## 5. Quản lý vòng đời container (lệnh hay dùng)

| Mục đích | Lệnh |
|----------|------|
| Build image | `docker compose build` hoặc `docker build -t mindigo-app .` |
| Chạy nền | `docker compose up -d` |
| Xem container đang chạy | `docker compose ps` hoặc `docker ps` |
| Xem tất cả image | `docker images` |
| Xem log | `docker compose logs -f app` |
| Vào shell trong container web | `docker compose exec app bash` |
| Vào MySQL trong container db | `docker compose exec db mysql -u mindigo -psecret mindigo` |
| Chạy lệnh artisan | `docker compose exec app php artisan migrate` |
| Dừng & xoá | `docker compose down` |

---

## 6. Port mapping & Volume — vì sao quan trọng?

- **Port mapping (`8080:80`)**: ánh xạ cổng `8080` của **máy chủ** → cổng `80` bên trong
  **container**. Người dùng ngoài truy cập `http://<ip-server>:8080`, Docker chuyển tiếp vào
  Apache (cổng 80) trong container. DB map `3307:3306` để không đụng MySQL cài sẵn trên host.
- **Volume (`db_data`, `storage_data`)**: gắn vùng lưu trữ bền vững **ngoài** vòng đời
  container. Container có thể bị xoá/khởi tạo lại nhưng **dữ liệu MySQL và file upload vẫn còn**.
  Nếu không có volume, `docker compose down` sẽ làm mất sạch dữ liệu.

---

## 7. Nội dung phục vụ thi vấn đáp

### 7.1. Container khác Máy ảo (VM) thế nào? Ưu điểm Docker?

| | Máy ảo (VM) | Container (Docker) |
|---|---|---|
| Ảo hoá | Ảo hoá **phần cứng**, mỗi VM có **OS riêng** đầy đủ | Ảo hoá **mức OS**, **dùng chung kernel** của host |
| Dung lượng | Hàng GB | Vài chục–vài trăm MB |
| Khởi động | Chậm (phút) | Nhanh (giây) |
| Tài nguyên | Nặng | Nhẹ |

**Ưu điểm Docker:** đóng gói app + thư viện + cấu hình thành 1 image → **chạy giống nhau ở mọi
máy** ("máy tôi chạy được" không còn là vấn đề); triển khai nhanh, dễ nhân bản, dễ rollback;
cô lập môi trường giữa các app.

### 7.2. Lệnh Docker cơ bản

```bash
docker build -t ten-image .      # build image từ Dockerfile
docker run -d -p 8080:80 ten-image   # chạy container, map cổng
docker ps                         # container đang chạy   (docker ps -a: tất cả)
docker images                     # danh sách image
docker exec -it <container> bash  # vào shell container
docker logs -f <container>        # xem log
docker stop/start/rm <container>  # dừng / chạy / xoá container
docker compose up -d / down       # chạy / dừng theo compose
```

### 7.3. Viết Dockerfile + docker-compose để chạy web + DB

Xem `Dockerfile` và `docker-compose.yml` của dự án này làm ví dụ mẫu: web container (php:apache)
và db container (mysql) khai báo cùng `networks`, db gắn `volumes` để lưu bền vững, `app` kết nối
db qua `DB_HOST=db`. Đây chính là yêu cầu "chạy đồng thời container web và container CSDL, kết
nối được với nhau".

### 7.4. Thực hành kiểm tra nhanh khi thi

```bash
docker compose up -d --build      # build + chạy
docker compose ps                 # thấy app + db ở trạng thái Up/healthy
curl -I http://localhost:8080     # kiểm tra web phản hồi (HTTP 200/302)
docker compose exec db mysql -u mindigo -psecret -e "SHOW TABLES;" mindigo   # kiểm tra DB
```

---

## 8. Phụ lục — các nội dung khác trong đề thi

> Phần này tóm tắt để trả lời/thực hành; không thuộc bộ file Docker nhưng nằm trong đề.

### 8.1. SSH & truyền file
```bash
ssh root@<ip-server>                 # đăng nhập server qua SSH
sftp root@<ip-server>                # truyền file qua SFTP (dựa trên SSH)
scp file.txt root@<ip>:/duong/dan    # copy nhanh 1 file qua SSH
```
FTP: cài `vsftpd` (`sudo apt install vsftpd`), bật service, kết nối bằng FileZilla.

### 8.2. SVN
```bash
svnadmin create /svn/duan            # tạo repository trên server
svn checkout svn://<ip>/duan         # client lấy code về
svn add . && svn commit -m "..."     # thêm & commit lên server
svn update                           # cập nhật code mới nhất
```

### 8.3. Git — lệnh cơ bản & xử lý xung đột
```bash
git init / clone <url>
git status / add . / commit -m "..." / push / pull
git branch / checkout -b <nhanh>
```
**Xử lý conflict khi merge:** sau khi `git pull`/`merge` báo xung đột, mở file có dấu
`<<<<<<<`, `=======`, `>>>>>>>`, chỉnh lại nội dung đúng, xoá các dấu đó, rồi:
```bash
git add <file-da-sua>
git commit            # hoàn tất merge
```

---

*Cập nhật cho Mindigo LMS — Laravel 12, PHP 8.3, MySQL 8, Vite/Tailwind v4.*
