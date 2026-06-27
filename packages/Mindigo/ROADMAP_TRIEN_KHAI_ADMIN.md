# Roadmap triển khai phần Quản trị (Admin) — Mindigo LMS

> Tài liệu mô tả **chức năng**, **liên kết dữ liệu (logic liên kết)** và **logic triển khai** cho các
> module lõi trong `packages/Mindigo/` (phần Admin + hạ tầng dùng chung). Song song với
> `packages/Teacher/ROADMAP_TRIEN_KHAI.md` và `packages/Students/ROADMAP_TRIEN_KHAI.md`.

## Quy ước chung

- **Namespace:** `Mindigo\<Module>` · **View / lang namespace:** theo từng package.
- **Phân quyền:** khu vực Admin yêu cầu `auth()->user()->role === 'admin'`; nhiều module dùng chung
  `RolePermission` để phân quyền chi tiết hơn (permission-based).
- **Vai trò của tầng này:** vừa là **trang quản trị toàn hệ thống**, vừa cung cấp **Model/hạ tầng dùng chung**
  cho Teacher & Student (User, Classroom, Exam, Question, Subject...).

### Hai nhóm package

| Nhóm | Package | Vai trò |
|---|---|---|
| **Hạ tầng dùng chung** | Auth, Core, ClassroomManagement, ExamManagement, QuestionBank, SubjectManagement, Profile | Cung cấp Model/nghiệp vụ nền cho cả 3 vai trò |
| **Quản trị (Admin UI)** | Dashboard, UserManagement, RolePermission, Report, AuditLog, SystemSetting, SupportManagement, BlogManagement | Trang quản trị, cấu hình, giám sát |

---

## A. Hạ tầng dùng chung

### Auth  (`LoginController`, `ForgotPasswordController`, `MindigoIdController`)
**Chức năng:** Đăng nhập/đăng xuất, quên mật khẩu, định danh Mindigo ID. Sở hữu `User` model (`role`: admin/teacher/student, `is_active`).
**Liên kết:** `users`; là gốc của mọi quan hệ `*_id` (teacher_id, student_id, user_id).
**Logic:** guard `web`; phân luồng sau đăng nhập theo `role` → dashboard tương ứng.

### Core  (`HomeController`)
**Chức năng:** Layout gốc, trang chủ, thành phần UI dùng chung, helper toàn cục.
**Liên kết:** không sở hữu dữ liệu nghiệp vụ; chứa middleware/layout chung.

### ClassroomManagement  (`ClassroomController`)
**Chức năng:** Quản trị lớp ở cấp hệ thống (Admin xem mọi lớp), sở hữu `Classroom`, pivot `classroom_students`, `classroom_subjects`.
**Liên kết:** Teacher/Student đều query model này. Quan hệ: `teacher()`, `students()` (many-to-many), `subjects()`.

### ExamManagement  (`ExamController`, `ExamAttemptController`)
**Chức năng:** Nghiệp vụ đề thi cấp hệ thống, sở hữu `Exam`, `ExamAttempt`, `ExamAttemptAnswer`, `ExamQuestion`.
**Liên kết:** TeacherExam tạo đề, StudentExam sinh attempt — đều dựa model ở đây.

### QuestionBank  (`QuestionBankController`)
**Chức năng:** Ngân hàng câu hỏi cấp hệ thống.
**Liên kết:** dùng bởi TeacherQuestion/TeacherExam và StudentPractice.

### SubjectManagement  (`SubjectController`)
**Chức năng:** CRUD môn học; gán môn vào lớp.
**Liên kết:** `subjects` ↔ `classroom_subjects`.

### Profile  (`ProfileController`)
**Chức năng:** Hồ sơ cá nhân dùng chung cho mọi vai trò (đổi thông tin, mật khẩu, avatar).
**Liên kết:** `users` của chính `auth()->id()`.

---

## B. Quản trị (Admin UI)

### Dashboard  (`DashboardController`, `SearchController`)  — `/admin` (hoặc `/dashboard`)
**Chức năng:** Bảng điều khiển toàn hệ thống — số liệu users/lớp/đề/bài, biểu đồ, tìm kiếm toàn cục.
**Liên kết:** chỉ-đọc, tổng hợp từ tất cả module.
**Logic:** `index()` gom số liệu; `SearchController` tìm kiếm chéo (user, lớp, đề...).

### UserManagement  (`UserManagementController`)
**Chức năng:** CRUD người dùng, gán vai trò, kích hoạt/khoá tài khoản (`is_active`), reset mật khẩu.
**Liên kết:** `users` + `RolePermission`.
**Logic:** resource controller; filter theo `role`/trạng thái; bulk action.

### RolePermission  (`RolePermissionController`)
**Chức năng:** Quản lý vai trò & quyền chi tiết, gán quyền cho vai trò/người dùng.
**Liên kết:** bảng roles/permissions/pivot; được middleware phân quyền toàn hệ thống dùng.
**Logic:** CRUD role + permission; gán quyền; cache phân quyền.

### Report  (`ReportController`)
**Chức năng:** Báo cáo tổng hợp (học tập, sử dụng hệ thống), xuất file.
**Liên kết:** chỉ-đọc đa nguồn (Exam, Assignment, Classroom...).
**Logic:** chọn loại báo cáo + khoảng thời gian → service tổng hợp → export (Excel/PDF).

### AuditLog  (`AuditLogController`)
**Chức năng:** Nhật ký thao tác toàn hệ thống (ai làm gì, khi nào).
**Liên kết:** bảng `audit_logs`; ghi qua observer/event ở các module.
**Logic:** `index` xem + filter theo user/hành động/thời gian; chỉ-đọc.

### SystemSetting  (`SystemSettingController`)
**Chức năng:** Cấu hình hệ thống (thông tin trường, năm học, branding, tham số mặc định).
**Liên kết:** bảng `settings` key-value; cache toàn cục.
**Logic:** form cập nhật theo nhóm cấu hình; clear cache khi lưu.

### SupportManagement  (`SupportTicketController`)
**Chức năng:** Quản lý ticket hỗ trợ từ GV/HS.
**Liên kết:** `support_tickets` (+ replies) gắn `user_id`.
**Logic:** list/assign/reply/close ticket; trạng thái + thông báo.

### BlogManagement  (`NewsController`)
**Chức năng:** Quản lý tin tức/blog hiển thị ngoài site.
**Liên kết:** bảng tin tức; public đọc qua Core/Home.
**Logic:** CRUD bài viết + trạng thái publish + ảnh.

---

## Việc cần rà soát (Admin)

- [ ] **Middleware vai trò 3 lớp** (`admin`/`teacher`/`student`) đồng bộ cho cả Teacher & Student vừa thêm.
- [ ] Kiểm `RolePermission` đã phủ các route Student mới (12 module) — bổ sung permission nếu cần.
- [ ] `AuditLog` ghi nhận thao tác của các module Student mới (nộp bài, làm thi...).
- [ ] `Report`/`Dashboard` bổ sung số liệu phía Student khi các module đó hoàn thành.
- [ ] `SystemSetting`: tham số dùng chung cho luồng Student (số lần thi, hạn nộp mặc định...).
```
