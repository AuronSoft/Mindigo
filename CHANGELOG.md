# Changelog

Tất cả thay đổi đáng chú ý của Mindigo LMS được ghi lại trong file này.

Định dạng theo [Keep a Changelog](https://keepachangelog.com/),
và dự án tuân theo [Semantic Versioning](https://semver.org/lang/vi/).

## [0.1.1] - 2026-06-27

### Tổng quan
Ra mắt **khu vực Học sinh (Student)** — kết nối luồng Học sinh vào nền tảng,
dựng khung hệ thống đầy đủ cho 12 module và hoàn thiện MVP cho trang Dashboard.
Học sinh đã có thể đăng nhập và truy cập khu vực riêng với giao diện đồng bộ
cùng khu vực Admin và Giáo viên.

### Added
- **12 package Student** (`packages/Students/*`) dựng theo chuẩn cấu trúc của hệ thống
  (composer, ServiceProvider, routes, controller, service, lang vi/en, views):
  Dashboard, Classroom, Assignment, Exam, Practice, Schedule, Progress, History,
  Leaderboard, Discussion, LiveSession, Notebook.
- **StudentDashboard — MVP đầy đủ:**
  - Service tổng hợp số liệu: lớp đang học, bài tập chưa nộp sắp đến hạn,
    đề thi đang mở, kết quả thi gần đây, điểm trung bình.
  - Giao diện dashboard: header gradient, 4 thẻ thống kê, các danh sách
    (bài tập / đề thi / lớp / kết quả) kèm empty-state.
- **Điều hướng Học sinh trong sidebar dùng chung** (`Mindigo-dashboard::layouts`):
  nhánh nav riêng cho học sinh gồm 5 nhóm (Tổng quan, Học tập, Theo dõi,
  Tương tác, Cá nhân) với 13 mục.
- **Tự động điều hướng sau đăng nhập** cho vai trò `student` về `student.dashboard`
  (`App\Support\RoleRedirector`).
- **Thẻ SEO meta** (`description`, `robots`) cho layout dashboard.
- **Tài khoản học sinh cố định** để kiểm thử trong seeder:
  `student@mindigo.com / 123456`.
- **Tài liệu roadmap triển khai** cho cả ba khu vực:
  `packages/Students/ROADMAP_TRIEN_KHAI.md`,
  `packages/Teacher/ROADMAP_TRIEN_KHAI.md`,
  `packages/Mindigo/ROADMAP_TRIEN_KHAI_ADMIN.md`.
- File `CHANGELOG.md`.

### Changed
- Đăng ký 12 package Student vào `composer.json` gốc (`require`, `autoload.psr-4`,
  `repositories`).
- Tài khoản Admin trong seeder chuyển sang `firstOrCreate` để seeder
  idempotent (chạy lại không lỗi trùng email).

### Security
- Toàn bộ route khu vực Student được bảo vệ bằng middleware `role:student|admin`.

### Notes
Sau khi pull về cần chạy:
- `composer update "mindigo/student-*"` — symlink package và auto-discover provider.
- `npm run dev` (hoặc `npm run build`) — biên dịch asset (dashboard dùng `@vite`).

[0.1.1]: https://github.com/scoppy9201/Mindigo/releases/tag/v0.1.1
