# Roadmap triển khai phần Học sinh (Student) — Mindigo LMS

> Tài liệu mô tả **chức năng**, **liên kết dữ liệu (logic liên kết)** và **logic triển khai** cho từng
> module trong `packages/Students/`. Dùng làm base chuẩn để cả team cùng phát triển.

## Quy ước chung

- **Namespace:** `Mindigo\Student<Module>` · **View namespace / lang:** `student-<module>`
- **Route:** prefix `student`, middleware `['web', 'auth']`, tên `student.<group>.*`
- **Phân quyền:** mọi controller phải kiểm tra `auth()->id()` là học sinh và **thuộc lớp** liên quan
  (chống truy cập chéo lớp — xem mục Bảo mật cuối file).
- **Tái dùng Model:** phần Student **không tạo lại bảng** đã có; chỉ query/ghi vào model của Teacher/Core.

### Bảng & Model dùng chung (nguồn dữ liệu)

| Dữ liệu | Model | Bảng |
|---|---|---|
| Người dùng | `Mindigo\Auth\Models\User` | `users` |
| Lớp học | `Mindigo\ClassroomManagement\Models\Classroom` | `classrooms` |
| HS trong lớp (pivot) | `ClassroomStudent` | `classroom_students` (`status`, `joined_at`) |
| Bài tập | `Mindigo\TeacherAssignment\Models\Assignment` | `assignments` |
| Bài nộp | `Mindigo\TeacherAssignment\Models\AssignmentSubmission` | `assignment_submissions` |
| Đề thi | `Mindigo\ExamManagement\Models\Exam` | `exams` |
| Lượt thi | `Mindigo\ExamManagement\Models\ExamAttempt` | `exam_attempts` (`user_id`) |
| Đáp án lượt thi | `ExamAttemptAnswer` | `exam_attempt_answers` |
| Thảo luận | `Mindigo\TeacherDiscussion\Models\{DiscussionThread,DiscussionMessage,DiscussionAttachment}` | `teacher_discussion_*` |
| Thông báo | `Mindigo\TeacherAnnouncement\Models\Announcement` | `announcements` |
| Ghi chú HS | `Mindigo\StudentNotebook\Models\Note` *(mới)* | `notes` |

> **Quan hệ gốc để lấy "lớp của học sinh":**
> `$classroomIds = Classroom::whereHas('students', fn($q) => $q->where('student_id', auth()->id())->where('classroom_students.status','active'))->pluck('id');`
> Hầu hết module Student đều bắt đầu từ `$classroomIds` này.

---

## Thứ tự triển khai (theo phụ thuộc)

```
GIAI ĐOẠN 1 — Nền tảng (bắt buộc, làm trước)
  1. StudentClassroom      ← gốc dữ liệu, mọi module khác lọc theo lớp HS
  2. StudentDashboard      ← tổng hợp, phụ thuộc các module khác (làm khung trước, ghép số liệu sau)

GIAI ĐOẠN 2 — Học tập cốt lõi
  3. StudentAssignment     ← phụ thuộc Classroom
  4. StudentExam           ← phụ thuộc Classroom + ExamManagement
  5. StudentSchedule       ← tổng hợp lịch từ Assignment + Exam + LiveSession

GIAI ĐOẠN 3 — Tương tác
  6. StudentDiscussion     ← phụ thuộc Classroom + TeacherDiscussion
  7. StudentLiveSession    ← phụ thuộc Classroom + TeacherLiveSession
  8. (Notification — module dùng chung, làm song song khi cần)

GIAI ĐOẠN 4 — Theo dõi & tự học
  9. StudentProgress       ← đọc kết quả từ Assignment + Exam
 10. StudentHistory        ← đọc lịch sử Attempt/Submission
 11. StudentPractice       ← phụ thuộc QuestionBank
 12. StudentNotebook       ← độc lập (bảng notes riêng)
 13. StudentLeaderboard    ← tổng hợp điểm, làm sau cùng
```

---

## Chi tiết từng module

### 1. StudentClassroom  `/student/classrooms`
**Chức năng:** HS xem danh sách lớp đang tham gia; vào 1 lớp xem tài liệu/bài giảng, thành viên, GV phụ trách, danh sách bài tập/đề thi/buổi học của lớp đó.

**Liên kết logic:**
- `Classroom` (lọc qua pivot `classroom_students` của HS hiện tại).
- Khi xem chi tiết: nạp `assignments`, `exams`, `discussionThreads`, `liveSessions` của lớp.

**Logic triển khai:**
- `index()` → list `$classroomIds` của HS, hiển thị card lớp.
- `show(Classroom $classroom)` → **bắt buộc** `assertStudentInClassroom($classroom)` rồi trả tài liệu lớp.
- Service: `getClassroomsForStudent($studentId)`, `getClassroomDetail($classroom, $studentId)`.
- Routes: `GET /` (index), `GET /{classroom}` (show).

---

### 2. StudentDashboard  `/student/dashboard`
**Chức năng:** Trang chủ HS — widget: số lớp, bài tập sắp đến hạn, đề thi sắp mở, thông báo mới, % tiến độ, vị trí leaderboard.

**Liên kết logic:** tổng hợp **chỉ-đọc** từ Assignment, Exam, Announcement, Progress, Leaderboard (lọc theo `$classroomIds`).

**Logic triển khai:**
- `index()` gọi `DashboardService::summary($studentId)` trả mảng widget.
- Làm khung + dữ liệu giả trước; ghép số liệu thật dần khi các module nguồn xong.
- Route: `GET /`.

---

### 3. StudentAssignment  `/student/assignments`
**Chức năng:** Xem bài tập được giao (chỉ `status=published`), xem đề + file đính kèm, **nộp bài** (file/text/cả hai theo `submission_type`), xem điểm + nhận xét sau khi GV chấm, nộp/cập nhật lại khi chưa quá hạn.

**Liên kết logic:**
- Đọc `Assignment` của các lớp HS (`classroom_id ∈ $classroomIds`, `status=published`).
- Ghi `AssignmentSubmission` (`assignment_id`, `student_id=auth()->id()`).
- Tính trễ hạn: so `now()` với `assignment.due_date` + `allow_late`/`late_days` → set `is_late`.

**Logic triển khai:**
- `index()` list bài tập, kèm trạng thái submission của HS (`submitted` / `chưa nộp` / `graded`).
- `show(Assignment $a)` → assert lớp; hiển thị đề + form nộp (ẩn/hiện field theo `allowsFile()`/`allowsText()`).
- `submit(SubmitAssignmentRequest, Assignment $a)`:
  - chặn nếu quá hạn & `!allow_late`;
  - lưu file vào `assignments/submissions` (disk `public`);
  - `updateOrCreate(['assignment_id','student_id'])` set `submitted_at=now()`, `is_late`, `status='submitted'`.
- **Request `SubmitAssignmentRequest`:** rule động theo `submission_type` (file: `mimes,max:20480`; text: `required|string`).
- Service: `getAssignmentsForStudent`, `findSubmission`, `submit`.
- Routes: `GET /`, `GET /{assignment}`, `POST /{assignment}/submit`, `GET /{assignment}/files/{i}` (tải đề).

---

### 4. StudentExam  `/student/exams`
**Chức năng:** Xem đề thi/quiz được giao, **làm bài** (đếm ngược theo `expires_at`, chống chuyển tab qua `tab_leave_count`), nộp bài, xem điểm + đáp án (nếu đề cho phép).

**Liên kết logic:**
- Đọc `Exam` gắn với lớp HS.
- Ghi `ExamAttempt` (`exam_id`, `user_id=auth()->id()`, `started_at`, `expires_at`, `status`) + `ExamAttemptAnswer`.
- Chấm tự động (trắc nghiệm) → `score`, `max_score`, `percentage`, `passed`.

**Logic triển khai:**
- `index()` list đề: sắp mở / đang mở / đã làm.
- `start(Exam $e)` → tạo `ExamAttempt status='in_progress'`, set `expires_at = now()+duration`; chặn nếu vượt số lần cho phép.
- `take(ExamAttempt $attempt)` → render câu hỏi theo `question_order`.
- `submit(SubmitExamRequest, ExamAttempt $attempt)` → lưu answers, auto-grade, set `submitted_at`, `status='submitted'`.
- `result(ExamAttempt $attempt)` → điểm + review.
- **Bảo mật:** mọi action assert `attempt.user_id === auth()->id()`.
- Routes: `GET /`, `POST /{exam}/start`, `GET /attempts/{attempt}`, `POST /attempts/{attempt}/submit`, `GET /attempts/{attempt}/result`.

---

### 5. StudentSchedule  `/student/schedule`
**Chức năng:** Lịch tổng hợp dạng calendar/list: hạn nộp bài tập, lịch mở đề thi, buổi học trực tuyến.

**Liên kết logic (chỉ-đọc, gộp 3 nguồn):**
- `Assignment.due_date`, `Exam` (thời gian mở/đóng), `LiveSession` (giờ bắt đầu) — đều lọc theo `$classroomIds`.

**Logic triển khai:**
- `index(Request)` nhận `from`/`to`; `ScheduleService::buildCalendar()` map mỗi nguồn thành item chuẩn `{type, title, datetime, url}` rồi sort theo thời gian.
- Route: `GET /`.

---

### 6. StudentDiscussion  `/student/discussions`
**Chức năng:** HS xem các thread thảo luận của lớp, đọc tin nhắn, **gửi tin nhắn + đính kèm file**.

**Liên kết logic:**
- Đọc `DiscussionThread` theo `classroom_id ∈ $classroomIds`; `DiscussionMessage` (+ `DiscussionAttachment`).
- Ghi `DiscussionMessage` với `sender_id=auth()->id()` (HS), `thread_id`.

**Logic triển khai:**
- `index()` list thread theo lớp; `show(thread)` → assert lớp, nạp messages phân trang.
- `store(StoreDiscussionMessageRequest, thread)` → tạo message + lưu attachment; bắn realtime/notification nếu có.
- Routes: `GET /`, `GET /{thread}`, `POST /{thread}/messages`.

---

### 7. StudentLiveSession  `/student/live-sessions`
**Chức năng:** Xem buổi học trực tuyến do GV mở, **vào phòng** khi tới giờ, xem buổi đã ghi (nếu có).

**Liên kết logic:**
- Đọc `LiveSession` (package `TeacherLiveSession`) theo lớp HS.
- (Tuỳ chọn) ghi điểm danh `live_session_attendances` khi HS tham gia.

**Logic triển khai:**
- `index()` list buổi: sắp diễn ra / đang live / đã kết thúc.
- `join(session)` → assert lớp + đúng khung giờ → redirect tới link phòng (Zoom/Meet/Jitsi) hoặc embed.
- Routes: `GET /`, `GET /{session}/join`.

---

### 8. StudentProgress  `/student/progress`
**Chức năng:** % hoàn thành theo lớp/môn: tỉ lệ bài tập đã nộp, đề thi đã làm, điểm trung bình, biểu đồ tiến bộ.

**Liên kết logic (chỉ-đọc):** đếm `AssignmentSubmission` / tổng `Assignment`; `ExamAttempt` / tổng `Exam`; trung bình `score`.

**Logic triển khai:**
- `ProgressService::computeForStudent($studentId, $classroomId=null)` trả `{assignment_rate, exam_rate, avg_score, timeline}`.
- Route: `GET /` (lọc theo `classroom_id` optional).

---

### 9. StudentHistory  `/student/history`
**Chức năng:** Nhật ký hoạt động: các bài đã nộp, lượt thi đã làm, điểm từng lần — có filter theo lớp/thời gian.

**Liên kết logic (chỉ-đọc):** `AssignmentSubmission` + `ExamAttempt` của `auth()->id()`, gộp & sort theo thời gian.

**Logic triển khai:**
- `index(Request)` merge 2 nguồn thành dòng lịch sử chuẩn `{type, title, score, date, url}`, phân trang.
- Route: `GET /`.

---

### 10. StudentPractice  `/student/practice`
**Chức năng:** Luyện tập tự do từ ngân hàng câu hỏi (không tính vào điểm chính), chọn môn/chủ đề/độ khó, làm và xem đáp án ngay.

**Liên kết logic:**
- Đọc `QuestionBank` (`Mindigo\QuestionBank\Models\*`).
- (Tuỳ chọn) lưu lượt luyện vào bảng riêng `practice_attempts` để thống kê — quyết định khi triển khai.

**Logic triển khai:**
- `index()` chọn bộ lọc; `start(StartPracticeRequest)` random N câu theo filter; `check()` chấm client/server.
- Routes: `GET /`, `POST /start`, `POST /check`.

---

### 11. StudentNotebook  `/student/notebook`
**Chức năng:** Ghi chú cá nhân (CRUD) — module **độc lập**, dữ liệu riêng của HS.

**Liên kết logic:**
- Model **mới** `Note` (`notes`: `student_id`, `title`, `content`, soft delete).
- Cần **migration** tạo bảng `notes` (chưa có) — đặt trong package này.

**Logic triển khai:**
- CRUD chuẩn: `index/create/store/edit/update/destroy`, mọi query `where('student_id', auth()->id())`.
- **Request `NoteRequest`:** `title required|max:255`, `content nullable|string`.
- Routes: resource `/student/notebook`.

---

### 12. StudentLeaderboard  `/student/leaderboard`
**Chức năng:** Bảng xếp hạng theo lớp/toàn trường dựa trên điểm tích luỹ; hiển thị hạng của HS hiện tại.

**Liên kết logic (chỉ-đọc):** tổng hợp điểm từ `ExamAttempt` (+ tuỳ chọn `AssignmentSubmission`) group theo `user_id`, lọc trong phạm vi lớp.

**Logic triển khai:**
- `LeaderboardService::ranking($scope, $classroomId=null)` trả top N + rank của HS.
- Nên **cache** (kết quả nặng, ít đổi).
- Route: `GET /`.

---

## Module dùng chung nên bổ sung (ngoài thư mục Students)

### Notification (đề xuất `packages/Mindigo/Notification`)
**Chức năng:** Trung tâm thông báo cho cả 3 vai trò — nhận `Announcement` của GV, nhắc deadline bài tập/đề thi, báo bài đã chấm.
**Liên kết:** dùng `notifications` table của Laravel + event/listener; HS đọc qua `auth()->user()->notifications`.
**Vì sao chung:** Admin/Teacher cũng cần → không nên nhét riêng vào Student.

---

## Bảo mật — checklist bắt buộc mỗi module

1. **Xác thực thuộc lớp:** trước khi trả bất kỳ tài nguyên gắn lớp → kiểm tra HS có trong `classroom_students` (status `active`). Gợi ý tạo trait/helper `assertStudentInClassroom(Classroom $c)` dùng chung.
2. **Sở hữu bản ghi:** submission/attempt/note phải có `student_id|user_id === auth()->id()` mới cho xem/sửa.
3. **Chặn thao tác hết hạn:** nộp bài/đề thi phải kiểm tra deadline & số lần cho phép ở **server** (không tin client).
4. **File:** chỉ stream qua route có kiểm quyền (`Storage::disk('public')->response`), không lộ path trực tiếp.
5. **Validate qua FormRequest:** không nhận input thô trong controller.

---

## Việc kỹ thuật còn thiếu cần làm

- [ ] Đăng ký 12 package vào `composer.json` gốc — **đã xong** (require + autoload + repositories).
- [ ] `composer update "mindigo/student-*"` để symlink vendor + auto-discover provider.
- [ ] **Migration mới:** bảng `notes` (StudentNotebook); cân nhắc `practice_attempts`, `live_session_attendances`.
- [ ] Middleware/Gate phân biệt vai trò `student` (chặn GV/Admin lọt vào route student và ngược lại).
- [ ] Layout/menu riêng cho HS (sidebar 12 module).
- [ ] Seeder dữ liệu mẫu HS-lớp để test luồng.
```
