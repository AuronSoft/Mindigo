# Roadmap triển khai phần Giáo viên (Teacher) — Mindigo LMS

> Tài liệu mô tả **chức năng**, **liên kết dữ liệu (logic liên kết)** và **logic triển khai** cho từng
> module trong `packages/Teacher/`. Phần lớn đã build — tài liệu này dùng để chuẩn hoá, rà soát còn thiếu
> và đối chiếu với phần Học sinh (`packages/Students/ROADMAP_TRIEN_KHAI.md`).

## Quy ước chung

- **Namespace:** `Mindigo\Teacher<Module>` · **View / lang namespace:** `teacher-<module>`
- **Route:** prefix `teacher`, middleware `['web', 'auth']`, tên `teacher.<group>.*`
- **Phân quyền:** mọi controller kiểm tra `auth()->user()->role === 'teacher'` và **chỉ thao tác tài nguyên do mình sở hữu** (`teacher_id === auth()->id()`).
- **Nguyên tắc:** Teacher là phía **tạo nội dung**; mỗi thứ GV tạo phải có "cửa nhận" tương ứng bên Student.

### Bảng & Model chính

| Dữ liệu | Model | Bảng |
|---|---|---|
| Người dùng | `Mindigo\Auth\Models\User` | `users` |
| Lớp học | `Mindigo\ClassroomManagement\Models\Classroom` | `classrooms` |
| HS trong lớp | `ClassroomStudent` (pivot) | `classroom_students` |
| Bài tập / Bài nộp | `TeacherAssignment\Models\{Assignment, AssignmentSubmission}` | `assignments`, `assignment_submissions` |
| Đề thi / Lượt thi | `ExamManagement\Models\{Exam, ExamAttempt, ExamAttemptAnswer}` | `exams`, `exam_attempts`, `exam_attempt_answers` |
| Câu hỏi | `QuestionBank\Models\*` | `questions`, ... |
| Thảo luận | `TeacherDiscussion\Models\{DiscussionThread, DiscussionMessage, DiscussionAttachment}` | `teacher_discussion_*` |
| Thông báo | `TeacherAnnouncement\Models\Announcement` | `announcements` |

> **Lấy "lớp của giáo viên":** `Classroom::where('teacher_id', auth()->id())->where('status','active')`.

---

## Trạng thái hiện tại

| Module | Controller | Trạng thái |
|---|---|---|
| TeacherDashboard | ✅ | Đã có |
| TeacherClassroom | ✅ | Đã có |
| TeacherAssignment | ✅ (Assignment + Submission) | Đã có |
| TeacherExam | ✅ | Đã có |
| TeacherQuestion | ✅ | Đã có |
| TeacherResult | ✅ | Đã có |
| TeacherAnnouncement | ✅ | Đã có |
| TeacherDiscussion | ✅ | Đã có |
| **TeacherLiveSession** | ❌ **rỗng** | **Cần build** |

---

## Chi tiết từng module

### 1. TeacherDashboard  `/teacher/dashboard`
**Chức năng:** Tổng quan của GV — số lớp, bài tập chờ chấm, đề thi đang mở, hoạt động gần đây.
**Liên kết:** tổng hợp chỉ-đọc từ Classroom, Assignment(+Submission), Exam, Discussion theo `teacher_id`.
**Logic:** `index()` → `DashboardService::summary($teacherId)` trả widget.

### 2. TeacherClassroom  `/teacher/classrooms`
**Chức năng:** CRUD lớp, thêm/xoá HS, gán môn học, xem danh sách thành viên.
**Liên kết:** `Classroom` (`teacher_id`), pivot `classroom_students`, `classroom_subjects`.
**Logic:** resource controller; `addStudent/removeStudent` thao tác pivot; `show` nạp `students`, `subjects`.

### 3. TeacherAssignment  `/teacher/assignments`
**Chức năng:** CRUD bài tập (file/text/both, hạn nộp, cho phép trễ, thang điểm), xem danh sách bài nộp, **chấm điểm + nhận xét**, trả bài hàng loạt.
**Liên kết:** `Assignment` (`teacher_id`, `classroom_id`) → `AssignmentSubmission` (`student_id`). HS nộp qua `StudentAssignment`.
**Logic:** `AssignmentController` CRUD + stream file; `SubmissionController` `index/grade/returnAll`; `AssignmentService::{create,update,delete,grade,getStudentSubmissionList,getStats}`.

### 4. TeacherExam  `/teacher/exams`
**Chức năng:** Tạo đề (gắn câu hỏi từ QuestionBank), cấu hình thời gian/số lần, mở cho lớp, xem lượt thi.
**Liên kết:** `Exam` ↔ `ExamQuestion` ↔ `QuestionBank`; HS làm sinh `ExamAttempt`/`ExamAttemptAnswer`.
**Logic:** CRUD đề; gán câu hỏi; publish cho `classroom_id`; auto-grade trắc nghiệm.

### 5. TeacherQuestion  `/teacher/questions`
**Chức năng:** CRUD ngân hàng câu hỏi của GV (nhiều loại: trắc nghiệm, tự luận...), gán môn/chủ đề/độ khó.
**Liên kết:** `QuestionBank\Models\*`; được TeacherExam và StudentPractice dùng lại.
**Logic:** resource controller; filter theo subject/topic/difficulty; import/export (nếu có).

### 6. TeacherResult  `/teacher/results`
**Chức năng:** Xem/tổng hợp kết quả theo đề (`by_exam`) hoặc theo học sinh (`by_student`), thống kê điểm.
**Liên kết:** chỉ-đọc `ExamAttempt` + `AssignmentSubmission` theo lớp/đề của GV.
**Logic:** `index`, `by_exam(exam)`, `by_student(student)` → service tổng hợp + xuất báo cáo.

### 7. TeacherAnnouncement  `/teacher/announcements`
**Chức năng:** CRUD thông báo gửi tới lớp.
**Liên kết:** `Announcement` (`teacher_id`, `classroom_id`); HS nhận qua Notification/StudentDashboard.
**Logic:** resource controller; khi publish → bắn notification cho HS trong lớp.

### 8. TeacherDiscussion  `/teacher/discussions`
**Chức năng:** Tạo thread thảo luận theo lớp, gửi tin nhắn + đính kèm, quản lý hội thoại.
**Liên kết:** `DiscussionThread` (`teacher_id`, `classroom_id`) → `DiscussionMessage` → `DiscussionAttachment`; HS tham gia qua `StudentDiscussion`.
**Logic:** `index/show`, `messages.store`; lưu attachment; realtime/notification.

### 9. TeacherLiveSession  `/teacher/live-sessions`  ⚠️ CHƯA BUILD
**Chức năng:** Tạo/lên lịch buổi học trực tuyến cho lớp, dán link phòng (Zoom/Meet/Jitsi), bắt đầu/kết thúc, xem điểm danh.
**Liên kết:** Model **mới** `LiveSession` (`teacher_id`, `classroom_id`, `start_at`, `join_url`, `status`); tuỳ chọn `live_session_attendances`. HS vào qua `StudentLiveSession`.
**Logic triển khai:**
- Dựng đủ khung như các package Teacher khác (composer, provider, routes, controller, service, request, views).
- **Migration mới:** `live_sessions` (+ `live_session_attendances` nếu điểm danh).
- `index/create/store/edit/update/destroy` + `start`/`end`; route prefix `teacher/live-sessions`.

---

## Việc còn thiếu (Teacher)

- [ ] **Build TeacherLiveSession** (đang rỗng) — ưu tiên, vì đã có `StudentLiveSession` chờ ghép.
- [ ] Migration `live_sessions` (+ attendances).
- [ ] Đảm bảo mỗi action publish (Assignment/Exam/Announcement/Discussion) đều bắn **Notification** tới HS.
- [ ] Middleware/Gate kiểm vai trò `teacher` đồng bộ với phần Student/Admin.
```
