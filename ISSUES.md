# Backlog Issues — Mindigo LMS

> Danh sách issue đề xuất để tạo trên GitHub (`scoppy9201/Quizbox`).
> Mỗi mục: copy **Title** vào ô tiêu đề, phần dưới `---` vào ô nội dung, gán **Labels** tương ứng.
> Chi tiết kỹ thuật xem trong:
> `packages/Students/ROADMAP_TRIEN_KHAI.md`, `packages/Teacher/ROADMAP_TRIEN_KHAI.md`, `packages/Mindigo/ROADMAP_TRIEN_KHAI_ADMIN.md`.

Gợi ý nhãn dùng chung: `student`, `teacher`, `admin`, `module`, `infra`, `migration`, `P1`, `P2`, `P3`.

---

## EPIC: Khu vực Học sinh (Student)

### #1 — [Student] StudentClassroom: xem lớp & tài liệu
**Labels:** `student` `module` `P1`

---
**Mô tả:** Học sinh xem danh sách lớp đang tham gia và chi tiết từng lớp.

**Chức năng**
- Danh sách lớp HS đang học (card).
- Chi tiết lớp: tài liệu/bài giảng, giáo viên, thành viên, danh sách bài tập/đề thi/buổi học của lớp.

**Liên kết dữ liệu:** `Classroom` (lọc qua pivot `classroom_students`), nạp `assignments`/`exams`/`discussionThreads`/`liveSessions` của lớp.

**Việc cần làm**
- [ ] `ClassroomController@index`, `@show` + `assertStudentInClassroom()`
- [ ] Service `getClassroomsForStudent`, `getClassroomDetail`
- [ ] Routes `GET /student/classrooms`, `GET /student/classrooms/{classroom}`
- [ ] View theo style dashboard hệ thống

**Phụ thuộc:** không (là gốc dữ liệu cho các module khác).

---

### #2 — [Student] StudentAssignment: xem & nộp bài tập
**Labels:** `student` `module` `P1`

---
**Mô tả:** HS xem bài tập được giao, nộp bài (file/text), xem điểm + nhận xét.

**Chức năng**
- Danh sách bài tập (`status=published`) kèm trạng thái nộp.
- Xem đề + file đính kèm; nộp/cập nhật khi chưa quá hạn (theo `submission_type`).
- Xem điểm + feedback sau khi GV chấm.

**Liên kết dữ liệu:** đọc `Assignment` (lớp HS), ghi `AssignmentSubmission` (`student_id`). Tính trễ hạn theo `due_date`/`allow_late`/`late_days`.

**Việc cần làm**
- [ ] `AssignmentController@index/show/submit`
- [ ] `SubmitAssignmentRequest` (rule động theo `submission_type`)
- [ ] Chặn nộp khi quá hạn & `!allow_late` (server-side)
- [ ] Routes `GET /student/assignments`, `GET /{assignment}`, `POST /{assignment}/submit`, `GET /{assignment}/files/{i}`

**Phụ thuộc:** #1

---

### #3 — [Student] StudentExam: làm bài thi & xem kết quả
**Labels:** `student` `module` `P1`

---
**Mô tả:** HS làm đề thi/quiz được giao, đếm ngược thời gian, nộp & xem kết quả.

**Chức năng**
- Danh sách đề: sắp mở / đang mở / đã làm.
- Làm bài: đếm ngược theo `expires_at`, đếm rời tab (`tab_leave_count`).
- Nộp → auto-grade trắc nghiệm; xem điểm + review (nếu đề cho phép).

**Liên kết dữ liệu:** đọc `Exam`, ghi `ExamAttempt`/`ExamAttemptAnswer` (`user_id`).

**Việc cần làm**
- [ ] `ExamController@index/start/take/submit/result`
- [ ] `SubmitExamRequest`
- [ ] Bảo mật: assert `attempt.user_id === auth()->id()`, kiểm số lần & deadline server-side
- [ ] Routes theo roadmap

**Phụ thuộc:** #1

---

### #4 — [Student] StudentSchedule: lịch tổng hợp
**Labels:** `student` `module` `P2`

---
**Mô tả:** Lịch dạng calendar/list gộp hạn nộp bài tập, lịch đề thi, buổi học trực tuyến.

**Liên kết dữ liệu:** `Assignment.due_date` + `Exam` + `LiveSession` (lọc theo lớp HS).

**Việc cần làm**
- [ ] `ScheduleController@index` (filter from/to)
- [ ] Service `buildCalendar()` chuẩn hoá item `{type,title,datetime,url}`
- [ ] View calendar/list

**Phụ thuộc:** #2, #3, #11

---

### #5 — [Student] StudentDiscussion: thảo luận lớp
**Labels:** `student` `module` `P2`

---
**Mô tả:** HS xem thread thảo luận của lớp, gửi tin nhắn + đính kèm.

**Liên kết dữ liệu:** đọc `DiscussionThread` (lớp HS) → `DiscussionMessage` (+ `DiscussionAttachment`); ghi message `sender_id=auth()->id()`.

**Việc cần làm**
- [ ] `DiscussionController@index/show/store`
- [ ] `StoreDiscussionMessageRequest`
- [ ] Routes `GET /student/discussions`, `GET /{thread}`, `POST /{thread}/messages`

**Phụ thuộc:** #1

---

### #6 — [Student] StudentLiveSession: vào lớp trực tuyến
**Labels:** `student` `module` `P2`

---
**Mô tả:** HS xem & vào buổi học trực tuyến do GV mở.

**Liên kết dữ liệu:** đọc `LiveSession` (lớp HS); tuỳ chọn ghi điểm danh.

**Việc cần làm**
- [ ] `LiveSessionController@index/join` (assert lớp + đúng khung giờ)
- [ ] Routes `GET /student/live-sessions`, `GET /{session}/join`

**Phụ thuộc:** #1, #12 (TeacherLiveSession)

---

### #7 — [Student] StudentProgress: tiến độ học tập
**Labels:** `student` `module` `P3`

---
**Mô tả:** % hoàn thành theo lớp/môn: tỉ lệ bài tập nộp, đề thi làm, điểm TB, biểu đồ.

**Liên kết dữ liệu (chỉ-đọc):** đếm `AssignmentSubmission`/`Assignment`, `ExamAttempt`/`Exam`, avg `score`.

**Việc cần làm**
- [ ] Service `computeForStudent($id, $classroomId=null)`
- [ ] `ProgressController@index`

**Phụ thuộc:** #2, #3

---

### #8 — [Student] StudentHistory: nhật ký hoạt động
**Labels:** `student` `module` `P3`

---
**Mô tả:** Lịch sử bài đã nộp + lượt thi đã làm, filter theo lớp/thời gian.

**Liên kết dữ liệu (chỉ-đọc):** merge `AssignmentSubmission` + `ExamAttempt` của HS, sort theo thời gian.

**Việc cần làm**
- [ ] `HistoryController@index` (merge + phân trang)

**Phụ thuộc:** #2, #3

---

### #9 — [Student] StudentPractice: luyện tập tự do
**Labels:** `student` `module` `P3`

---
**Mô tả:** Luyện tập từ ngân hàng câu hỏi (không tính điểm chính), chọn môn/chủ đề/độ khó.

**Liên kết dữ liệu:** đọc `QuestionBank`; tuỳ chọn lưu `practice_attempts`.

**Việc cần làm**
- [ ] `PracticeController@index/start/check`
- [ ] `StartPracticeRequest`
- [ ] (Tuỳ chọn) migration `practice_attempts`

**Phụ thuộc:** QuestionBank

---

### #10 — [Student] StudentNotebook: ghi chú cá nhân
**Labels:** `student` `module` `migration` `P3`

---
**Mô tả:** CRUD ghi chú cá nhân — dữ liệu riêng của HS.

**Liên kết dữ liệu:** Model mới `Note` (bảng `notes`).

**Việc cần làm**
- [ ] **Migration `notes`** (`student_id`, `title`, `content`, softDeletes)
- [ ] CRUD `NotebookController` (mọi query `where student_id = auth()->id()`)
- [ ] `NoteRequest`

**Phụ thuộc:** không

---

### #11 — [Student] StudentLeaderboard: bảng xếp hạng
**Labels:** `student` `module` `P3`

---
**Mô tả:** Xếp hạng theo lớp/toàn trường dựa trên điểm tích luỹ, hiển thị hạng của HS.

**Liên kết dữ liệu (chỉ-đọc):** tổng hợp điểm `ExamAttempt` (+ tuỳ chọn `AssignmentSubmission`) group theo `user_id`.

**Việc cần làm**
- [ ] Service `ranking($scope, $classroomId=null)` + cache
- [ ] `LeaderboardController@index`

**Phụ thuộc:** #3

---

## EPIC: Giáo viên (Teacher)

### #12 — [Teacher] TeacherLiveSession: tạo & quản lý buổi học trực tuyến
**Labels:** `teacher` `module` `migration` `P1`

---
**Mô tả:** Package `TeacherLiveSession` đang **rỗng** — cần build để khớp với `StudentLiveSession`.

**Chức năng**
- Tạo/lên lịch buổi học cho lớp, dán link phòng (Zoom/Meet/Jitsi), bắt đầu/kết thúc, xem điểm danh.

**Liên kết dữ liệu:** Model mới `LiveSession` (`teacher_id`, `classroom_id`, `start_at`, `join_url`, `status`); tuỳ chọn `live_session_attendances`.

**Việc cần làm**
- [ ] Dựng đủ khung package (composer/provider/routes/controller/service/request/views)
- [ ] **Migration `live_sessions`** (+ `live_session_attendances`)
- [ ] CRUD + `start`/`end`; route prefix `teacher/live-sessions`
- [ ] Đăng ký package vào `composer.json` gốc

**Phụ thuộc:** không (liên quan #6)

---

## EPIC: Hạ tầng dùng chung (Infra)

### #13 — [Infra] Module Notification dùng chung
**Labels:** `infra` `module` `P2`

---
**Mô tả:** Trung tâm thông báo cho cả 3 vai trò: nhận `Announcement`, nhắc deadline bài tập/đề thi, báo bài đã chấm.

**Liên kết dữ liệu:** bảng `notifications` của Laravel + event/listener; HS đọc qua `auth()->user()->notifications`.

**Việc cần làm**
- [ ] Tạo package `packages/Mindigo/Notification`
- [ ] Migration `notifications`
- [ ] Event/Listener khi GV publish Assignment/Exam/Announcement/Discussion
- [ ] UI chuông thông báo trong layout dùng chung

**Phụ thuộc:** TeacherAnnouncement, #2, #3

---

### #14 — [Infra] Middleware/Gate phân tách vai trò 3 lớp
**Labels:** `infra` `P1`

---
**Mô tả:** Đảm bảo `admin`/`teacher`/`student` không truy cập chéo khu vực; rà soát toàn bộ route.

**Việc cần làm**
- [ ] Chuẩn hoá middleware `role:` cho mọi nhóm route (student đã có `role:student|admin`)
- [ ] Helper/Trait `assertStudentInClassroom()` dùng chung cho các module Student
- [ ] Kiểm `RolePermission` đã phủ các route Student mới

**Phụ thuộc:** xuyên suốt

---

### #15 — [Infra] AuditLog ghi nhận hoạt động phía Student
**Labels:** `infra` `P3`

---
**Mô tả:** Ghi log các thao tác mới của HS (nộp bài, làm thi, ghi chú...).

**Việc cần làm**
- [ ] Gắn observer/event cho `AssignmentSubmission`, `ExamAttempt`, `Note`
- [ ] Hiển thị trong `AuditLog` admin

**Phụ thuộc:** #2, #3, #10
