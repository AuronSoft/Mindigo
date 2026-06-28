# 4. Ghi chú Route/API của Mindigo LMS

Mindigo LMS hiện ưu tiên mô hình **web application dùng session + Blade** thay vì REST API
thuần token. Vì vậy phần lớn endpoint nằm trong `routes/web.php` của từng module, có CSRF,
middleware đăng nhập và phân quyền theo vai trò.

---

## 1. Nguyên tắc route hiện tại

| Thành phần | Cách dùng |
|------------|-----------|
| Web route | Khai báo trong `packages/.../routes/web.php`. |
| Middleware | Bảo vệ route theo đăng nhập/vai trò. |
| Route name | Dùng trong view/controller, ví dụ `teacher.assignments.index`. |
| Controller | Nhận request, gọi service, trả view/redirect. |
| Form POST/PUT/DELETE | Dùng CSRF token của Laravel. |
| File upload/download | Đi qua storage/public link hoặc route download có kiểm quyền. |

Xem route toàn hệ thống:

```bash
php artisan route:list
```

Lọc route theo nhóm:

```bash
php artisan route:list --path=teacher
php artisan route:list --path=student
php artisan route:list --name=teacher.assignments
```

---

## 2. Nhóm route theo vai trò

### 2.1. Auth/Public

| Nhóm | Mục đích |
|------|----------|
| Login/Logout | Đăng nhập và thoát phiên. |
| Mindigo ID OTP | Gửi/xác thực mã OTP. |
| Magic link | Đăng nhập nhanh qua link email. |
| Password reset | Quên mật khẩu/đặt lại mật khẩu. |

Các route này thường không nằm trong prefix teacher/student, nhưng sau khi đăng nhập sẽ điều
hướng người dùng về dashboard đúng vai trò.

### 2.2. Admin/Core

| Prefix/Module | Chức năng |
|---------------|-----------|
| `/dashboard` | Trang tổng quan/quản trị. |
| `/question-bank` | Ngân hàng câu hỏi. |
| `/exams` | Quản lý đề thi cấp hệ thống. |
| `/classrooms` | Quản lý lớp học. |
| `/subjects` | Môn học và chủ đề. |
| `/users` | Quản lý người dùng. |
| `/reports` | Báo cáo. |
| `/support-tickets` | Hỗ trợ người dùng. |
| `/system-settings` | Cấu hình hệ thống. |
| `/audit-logs` | Nhật ký hoạt động. |
| `/profile` | Hồ sơ cá nhân. |

### 2.3. Teacher

| Route | Chức năng |
|-------|-----------|
| `/teacher` | Dashboard giáo viên. |
| `/teacher/courses` | Khoá học/môn học của giáo viên. |
| `/teacher/classrooms` | Lớp học của giáo viên. |
| `/teacher/exams` | Tạo và quản lý đề thi. |
| `/teacher/assignments` | Bài tập đã giao. |
| `/teacher/assignments/grading` | Chấm điểm bài nộp. |
| `/teacher/questions` | Câu hỏi của giáo viên. |
| `/teacher/results` | Kết quả học tập. |
| `/teacher/reports` | Báo cáo giáo viên. |
| `/teacher/discussions` | Trao đổi nội bộ lớp. |
| `/teacher/live-sessions` | Buổi học trực tuyến. |
| `/teacher/announcements` | Thông báo lớp học. |

### 2.4. Student

| Route | Chức năng |
|-------|-----------|
| `/student` | Dashboard học sinh. |
| `/student/classrooms` | Lớp học của học sinh. |
| `/student/exams` | Bài thi/kiểm tra. |
| `/student/assignments` | Bài tập cần làm/nộp. |
| `/student/practice` | Luyện tập. |
| `/student/schedule` | Lịch học. |
| `/student/progress` | Tiến độ học tập. |
| `/student/history` | Lịch sử làm bài. |
| `/student/leaderboard` | Bảng xếp hạng. |
| `/student/notebook` | Sổ tay. |
| `/student/discussions` | Trao đổi lớp học. |
| `/student/live-sessions` | Buổi học trực tuyến. |

---

## 3. Quy ước đặt tên route

Route name nên theo cấu trúc:

```text
<role-or-module>.<resource>.<action>
```

Ví dụ:

```text
teacher.assignments.index
teacher.assignments.create
teacher.assignments.store
teacher.assignments.grading
teacher.discussions.index
student.live-sessions.index
```

Lợi ích:

- View không phụ thuộc URL cứng.
- Đổi URL nhưng không phải sửa nhiều nơi.
- Dễ lọc bằng `php artisan route:list --name=...`.
- Sidebar/menu có thể active đúng theo route name.

---

## 4. Request/Response trong web route

### 4.1. GET

Dùng để hiển thị trang hoặc lọc dữ liệu:

```text
GET /teacher/assignments?classroom_id=1&search=grammar
```

Controller trả về Blade view:

```php
return view('teacher-assignment::index', $data);
```

### 4.2. POST

Dùng để tạo dữ liệu mới, cần CSRF:

```blade
<form method="POST" action="{{ route('teacher.assignments.store') }}">
    @csrf
</form>
```

### 4.3. PUT/PATCH

Dùng để cập nhật:

```blade
@method('PUT')
```

### 4.4. DELETE

Dùng để xoá mềm/xoá dữ liệu khi có quyền:

```blade
@method('DELETE')
```

---

## 5. Upload và file public

Luồng upload chuẩn:

1. Validate file trong controller/request.
2. Lưu file vào disk phù hợp, thường là `public`.
3. Chạy `php artisan storage:link`.
4. Hiển thị file qua `Storage::url($path)` hoặc route download có kiểm quyền.

Ví dụ:

```php
$path = $request->file('attachment')->store('assignments', 'public');
```

Khi file không xem được, kiểm tra:

```bash
php artisan storage:link
php artisan optimize:clear
```

---

## 6. Nếu mở REST API trong tương lai

Khi cần API cho mobile/app ngoài, nên tách sang `routes/api.php` hoặc package route riêng:

```php
Route::prefix('api/v1')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/teacher/assignments', [AssignmentApiController::class, 'index']);
    });
```

Khuyến nghị:

- Dùng version prefix: `/api/v1`.
- Dùng `auth:sanctum` cho token.
- Trả JSON resource/DTO, không trả Blade.
- Validate bằng FormRequest.
- Chuẩn hoá lỗi: `message`, `errors`, `code`.
- Không dùng chung controller web nếu logic response khác nhau nhiều.

Ví dụ response JSON:

```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "total": 0
  }
}
```

---

## 7. Checklist khi thêm route mới

- Đặt route trong đúng package.
- Có prefix và name rõ ràng.
- Có middleware đăng nhập/phân quyền.
- Không hard-code URL trong Blade, dùng `route(...)`.
- Form POST/PUT/DELETE có `@csrf`.
- Controller gọi service thay vì xử lý quá nhiều logic trực tiếp.
- Text hiển thị đưa vào lang file.
- Chạy `php artisan route:list` để kiểm tra trùng route.

---

## 8. Nội dung phục vụ thi vấn đáp

- **Web route** dùng session, cookie, CSRF và trả HTML.
- **API route** thường stateless, trả JSON và xác thực bằng token.
- **CSRF** bảo vệ form khỏi request giả mạo từ website khác.
- **Route name** giúp tách URL khỏi code gọi route.
- **Middleware** kiểm soát đăng nhập, phân quyền và điều kiện truy cập.
- **REST API** nên có version, chuẩn response và cơ chế authentication rõ ràng.

---

*Cập nhật cho Mindigo LMS — route web theo module, sẵn sàng mở rộng API khi cần.*
