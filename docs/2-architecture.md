# 2. Kiến trúc hệ thống Mindigo LMS

Tài liệu này mô tả kiến trúc hiện tại của **Mindigo LMS**: cách chia module, luồng xử lý,
vai trò người dùng và nguyên tắc mở rộng chức năng. Mục tiêu là giúp đọc code nhanh, sửa
đúng vị trí và giải thích được hệ thống khi demo/thi vấn đáp.

---

## 1. Tổng quan kiến trúc

Mindigo LMS được xây trên Laravel theo hướng **module/package nội bộ**. Thay vì để tất cả
code trong một thư mục lớn, hệ thống chia theo miền nghiệp vụ:

```text
Mindigo/
├── app/                     # phần Laravel gốc
├── packages/
│   ├── Mindigo/              # module lõi/quản trị
│   ├── Teacher/              # module cho giáo viên
│   └── Students/             # module cho học sinh
├── database/                 # migration, seeder
├── resources/                # tài nguyên chung
├── public/                   # web root
└── routes/                   # route mặc định của app gốc
```

Mỗi package thường có cấu trúc:

```text
src/
├── Http/Controllers/
├── Models/
├── Services/
├── Providers/
├── routes/web.php
└── resources/
    ├── views/
    ├── lang/
    └── css/
```

---

## 2. Luồng xử lý chuẩn

Luồng request điển hình trong hệ thống:

```text
Browser
  ↓
Route trong packages/.../routes/web.php
  ↓
Controller
  ↓
Service xử lý nghiệp vụ
  ↓
Model/Eloquent + Database
  ↓
Blade view + lang file
  ↓
Response trả về trình duyệt
```

Ví dụ với chức năng bài tập của giáo viên:

| Tầng | Vai trò |
|------|---------|
| Route | Khai báo URL `/teacher/assignments`, tên route và middleware. |
| Controller | Nhận request, gọi service, trả view hoặc redirect. |
| Service | Xử lý logic tạo bài, lọc lớp, thống kê bài nộp. |
| Model | Làm việc với bảng assignments/submissions. |
| View | Hiển thị danh sách, form, modal lọc, trạng thái rỗng. |
| Lang | Tách text ra `resources/lang` để hỗ trợ đa ngôn ngữ. |

---

## 3. Nhóm module chính

### 3.1. Module lõi/quản trị (`packages/Mindigo`)

| Module | Chức năng |
|--------|-----------|
| `Auth` | Đăng nhập, OTP/Mindigo ID, magic link, reset mật khẩu. |
| `Dashboard` | Layout chung, sidebar, giao diện khung hệ thống. |
| `QuestionBank` | Ngân hàng câu hỏi, folder, chủ đề, duyệt câu hỏi. |
| `ExamManagement` | Quản lý đề thi phía quản trị. |
| `ClassroomManagement` | Quản lý lớp học và thành viên. |
| `SubjectManagement` | Môn học, chủ đề/bài học. |
| `UserManagement` | Người dùng, vai trò, hồ sơ. |
| `RolePermission` | Phân quyền theo role/permission. |
| `Report` | Báo cáo vận hành và báo cáo giáo viên. |
| `Notification` | Thông báo trong hệ thống. |
| `SupportManagement` | Hỗ trợ/yêu cầu từ người dùng. |
| `SystemSetting` | Cấu hình hệ thống. |
| `AuditLog` | Ghi nhận hoạt động quan trọng. |
| `Profile` | Hồ sơ cá nhân. |

### 3.2. Module giáo viên (`packages/Teacher`)

| Module | Chức năng |
|--------|-----------|
| `TeacherDashboard` | Bảng điều khiển giáo viên. |
| `TeacherCourse` | Khoá học/môn học của giáo viên. |
| `TeacherClassroom` | Lớp học của giáo viên. |
| `TeacherExam` | Tạo và quản lý đề thi. |
| `TeacherAssignment` | Giao bài, chấm bài, theo dõi bài nộp. |
| `TeacherQuestion` | Câu hỏi của giáo viên/ngân hàng câu hỏi. |
| `TeacherResult` | Kết quả học tập, điểm, lượt làm bài. |
| `TeacherDiscussion` | Trao đổi nội bộ lớp dạng hộp chat. |
| `TeacherLiveSession` | Buổi học trực tuyến. |
| `TeacherAnnouncement` | Thông báo cho lớp/khoá học. |

### 3.3. Module học sinh (`packages/Students`)

| Module | Chức năng |
|--------|-----------|
| `StudentDashboard` | Bảng điều khiển học sinh. |
| `StudentClassroom` | Lớp học đang tham gia. |
| `StudentExam` | Làm bài thi/kiểm tra. |
| `StudentAssignment` | Xem và nộp bài tập. |
| `StudentPractice` | Luyện tập. |
| `StudentSchedule` | Lịch học/lịch kiểm tra. |
| `StudentProgress` | Tiến độ học tập. |
| `StudentHistory` | Lịch sử làm bài. |
| `StudentLeaderboard` | Bảng xếp hạng. |
| `StudentNotebook` | Sổ tay học tập. |
| `StudentDiscussion` | Trao đổi trong lớp. |
| `StudentLiveSession` | Buổi học trực tuyến của học sinh. |

---

## 4. Nguyên tắc phân vai

Hệ thống chia 3 luồng chính:

| Vai trò | Khu vực | Mục tiêu |
|---------|---------|----------|
| Admin | `/dashboard`, các module quản trị | Quản lý người dùng, lớp, môn, ngân hàng câu hỏi, hệ thống. |
| Teacher | `/teacher/...` | Tạo đề, giao bài, chấm điểm, quản lý lớp, trao đổi, xem báo cáo. |
| Student | `/student/...` | Học, làm bài, nộp bài, xem tiến độ, tham gia lớp/live session. |

Middleware và route prefix giúp tách rõ quyền truy cập. Khi thêm chức năng mới cần đặt route
đúng nhóm để tránh học sinh vào được trang giáo viên hoặc ngược lại.

---

## 5. Dữ liệu nghiệp vụ chính

| Nhóm dữ liệu | Ý nghĩa |
|--------------|---------|
| Users/Roles | Người dùng, vai trò admin/teacher/student và quyền truy cập. |
| Subjects/Topics | Môn học và chủ đề/bài học. |
| Classrooms | Lớp học, danh sách học sinh, môn gắn với lớp. |
| Question Bank | Câu hỏi, đáp án, folder, chủ đề, trạng thái duyệt. |
| Exams | Đề thi, câu hỏi trong đề, lượt làm bài, câu trả lời. |
| Assignments | Bài tập, bài nộp, file đính kèm, trạng thái chấm. |
| Discussions | Nhóm chat lớp, tin nhắn, ảnh, file, thành viên. |
| Reports | Thống kê lớp, kết quả, bài nộp, hiệu suất học tập. |
| Notifications | Thông báo on-app cho người dùng. |

---

## 6. Giao diện và ngôn ngữ

- Layout chung nằm trong module `Mindigo/Dashboard`.
- Mỗi module có view Blade riêng trong `resources/views`.
- Text hiển thị nên đọc từ file lang, không hard-code trực tiếp trong view.
- File lang thường đặt tại:

```text
packages/<Vendor>/<Module>/src/resources/lang/vi/app.php
packages/<Vendor>/<Module>/src/resources/lang/en/app.php
```

Quy tắc đặt key:

```php
return [
    'title' => '...',
    'actions' => [
        'create' => '...',
    ],
];
```

---

## 7. Thêm module mới

Các bước chuẩn:

1. Tạo package trong đúng nhóm: `packages/Mindigo`, `packages/Teacher` hoặc `packages/Students`.
2. Tạo `composer.json` cho package, khai báo PSR-4 autoload và service provider.
3. Tạo service provider để load route/view/lang:

```php
$this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
$this->loadViewsFrom(__DIR__ . '/../../resources/views', 'package-name');
$this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'package-name');
```

4. Khai báo route có prefix, name và middleware rõ ràng.
5. Tách logic nghiệp vụ vào service, không nhồi toàn bộ vào controller.
6. Tạo view theo style hệ thống, dùng lang key.
7. Chạy:

```bash
composer dump-autoload
php artisan optimize:clear
php artisan route:list
```

---

## 8. Nguyên tắc code trong dự án

- Controller mỏng: nhận request, validate, gọi service, trả response.
- Service chứa nghiệp vụ chính.
- Model chỉ nên giữ quan hệ, scope, cast và helper liên quan dữ liệu.
- View không xử lý logic phức tạp.
- Route name cần nhất quán, ví dụ `teacher.assignments.index`.
- Khi có text mới trên UI, thêm lang key `vi` và `en`.
- Khi có upload file, lưu qua storage và tạo link public đúng chuẩn.

---

## 9. Nội dung phục vụ thi vấn đáp

- **MVC**: Model quản lý dữ liệu, View hiển thị, Controller điều phối request.
- **Service layer** giúp tách nghiệp vụ khỏi controller, code dễ test và dễ bảo trì.
- **Module/package architecture** giúp chia hệ thống lớn thành các phần nhỏ theo nghiệp vụ.
- **Middleware** kiểm soát xác thực, phân quyền và các điều kiện truy cập.
- **Migration/Seeder** giúp đồng bộ database và dữ liệu mẫu giữa nhiều môi trường.
- **Localization** giúp hệ thống hỗ trợ nhiều ngôn ngữ mà không sửa trực tiếp view.

---

*Cập nhật cho Mindigo LMS — kiến trúc module Laravel theo nhóm Admin/Teacher/Student.*
