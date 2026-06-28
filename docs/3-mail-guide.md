# 3. Hướng dẫn cấu hình Mail trong Mindigo LMS

Tài liệu này hướng dẫn cấu hình gửi email cho **Mindigo LMS**. Hệ thống dùng mail cho các
luồng xác thực như OTP/Mindigo ID, magic link, reset mật khẩu và có thể mở rộng cho thông
báo lớp học, bài tập, đề thi.

---

## 1. Thành phần mail trong hệ thống

| Thành phần | Vai trò |
|------------|---------|
| `.env` | Khai báo SMTP/log/mail provider. |
| `config/mail.php` | Cấu hình mail chuẩn của Laravel. |
| `packages/Mindigo/Auth/src/Mail/` | Các class mail cho xác thực Mindigo ID/OTP/magic link. |
| Blade email view | Giao diện nội dung email. |
| Queue | Chạy gửi mail nền nếu mail được dispatch qua queue. |

Các mail class hiện có:

```text
packages/Mindigo/Auth/src/Mail/MindigoIdOtpMail.php
packages/Mindigo/Auth/src/Mail/MindigoIdMagicLinkMail.php
```

---

## 2. Cấu hình mail local

### 2.1. Ghi mail vào log khi phát triển

Cách này an toàn nhất khi code local vì không gửi email thật:

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@mindigo.local"
MAIL_FROM_NAME="Mindigo LMS"
```

Sau khi thao tác gửi mail, xem log:

```bash
tail -f storage/logs/laravel.log
```

Trên Windows có thể mở trực tiếp file `storage/logs/laravel.log`.

### 2.2. Dùng SMTP thật hoặc dịch vụ test

Ví dụ cấu hình SMTP:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@mindigo.edu.vn"
MAIL_FROM_NAME="Mindigo LMS"
```

Với Mailtrap/Mailpit/Mailhog, thay `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`,
`MAIL_PASSWORD` theo thông tin provider cung cấp.

Sau khi đổi `.env`, chạy:

```bash
php artisan optimize:clear
```

---

## 3. Kiểm tra gửi mail

Gửi thử một email đơn giản:

```bash
php artisan tinker --execute="Mail::raw('Mindigo mail test', fn ($m) => $m->to('you@example.com')->subject('Mindigo LMS test'));"
```

Nếu dùng `MAIL_MAILER=log`, nội dung sẽ nằm trong `storage/logs/laravel.log`. Nếu dùng SMTP,
kiểm tra hộp thư hoặc dashboard của dịch vụ mail test.

---

## 4. Gửi mail qua queue

Nếu mail class dùng `ShouldQueue` hoặc hệ thống dispatch job gửi mail, cần chạy worker:

```bash
php artisan queue:listen
```

Khi chạy phát triển bằng:

```bash
composer run dev
```

queue listener đã được chạy cùng Laravel server, Vite và log viewer.

Trong production nên dùng supervisor/systemd để giữ queue worker chạy ổn định.

---

## 5. HTML email và inline CSS

Email client như Gmail/Outlook thường không hỗ trợ CSS giống trình duyệt. Vì vậy CSS trong
email nên được inline để hiển thị ổn định.

Package dùng cho việc inline CSS:

```bash
composer require tijsverkoyen/css-to-inline-styles
```

Trong dự án, package này phục vụ việc render HTML email tương thích hơn với nhiều email
client. Khi thiết kế template mail:

- Dùng layout đơn giản, ít phụ thuộc JavaScript.
- Ưu tiên table/layout cơ bản nếu cần tương thích cao.
- Tránh style phức tạp không được email client hỗ trợ.
- Luôn test trên ít nhất một webmail và một mail client phổ biến.

---

## 6. Quy trình thêm email mới

1. Tạo mailable:

```bash
php artisan make:mail ExampleMail
```

2. Đặt class vào module phù hợp nếu email thuộc nghiệp vụ package, ví dụ:

```text
packages/Teacher/TeacherAssignment/src/Mail/AssignmentPublishedMail.php
```

3. Tạo view email trong package:

```text
packages/Teacher/TeacherAssignment/src/resources/views/emails/assignment-published.blade.php
```

4. Gửi mail từ service, không gửi trực tiếp trong view:

```php
Mail::to($student->email)->send(new AssignmentPublishedMail($assignment));
```

5. Nếu số lượng lớn, chuyển sang queue:

```php
Mail::to($student->email)->queue(new AssignmentPublishedMail($assignment));
```

---

## 7. Lỗi thường gặp

### 7.1. Không thấy email gửi đi

- Kiểm tra `MAIL_MAILER`.
- Nếu dùng log, xem `storage/logs/laravel.log`.
- Nếu dùng queue, kiểm tra worker đã chạy chưa.
- Chạy `php artisan optimize:clear` sau khi đổi `.env`.

### 7.2. SMTP báo sai tài khoản/mật khẩu

- Kiểm tra `MAIL_USERNAME`, `MAIL_PASSWORD`.
- Với Gmail hoặc provider bảo mật cao, có thể cần app password.
- Kiểm tra port/encryption: `587/tls`, `465/ssl`, hoặc port riêng của provider.

### 7.3. Email vào spam

- Dùng địa chỉ gửi cùng domain thật.
- Cấu hình SPF/DKIM/DMARC khi deploy production.
- Không gửi quá nhiều mail test trùng nội dung.
- Tiêu đề và nội dung nên rõ ràng, không giống spam.

### 7.4. Giao diện email vỡ layout

- Giảm CSS phức tạp.
- Inline CSS.
- Test trên Gmail/Outlook.
- Tránh phụ thuộc font ngoài hoặc script.

---

## 8. Nội dung phục vụ thi vấn đáp

- **SMTP** là giao thức gửi email từ ứng dụng tới mail server.
- **MAIL_MAILER=log** giúp test an toàn mà không gửi mail thật.
- **Queue** giúp gửi mail nền, tránh làm request chậm khi gửi nhiều email.
- **Inline CSS** giúp HTML email hiển thị ổn định trên nhiều mail client.
- **SPF/DKIM/DMARC** là các cấu hình DNS giúp email production đáng tin cậy hơn và giảm spam.

---

*Cập nhật cho Mindigo LMS — mail xác thực, OTP/Magic Link và thông báo hệ thống.*
