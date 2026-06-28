# StudentPractice - Cấu trúc phụ thuộc với QuestionBank

## 🎯 Tổng quan

StudentPractice là module dành cho học sinh để luyện tập với các câu hỏi. Module này **phụ thuộc hoàn toàn vào QuestionBank** làm nguồn dữ liệu câu hỏi duy nhất.

## 📋 Phân chia trách nhiệm

### 🏦 QuestionBank - Quản lý câu hỏi
QuestionBank chịu trách nhiệm **duy nhất** cho:
- ✅ Tạo (Create) câu hỏi mới
- ✅ Sửa (Update) câu hỏi
- ✅ Xóa (Delete) câu hỏi
- ✅ Quản lý trạng thái câu hỏi (draft, reviewing, approved, rejected)
- ✅ Lưu trữ đáp án chính xác
- ✅ Quản lý mức độ khó (easy, medium, hard)
- ✅ Quản lý môn học, chủ đề, loại câu hỏi
- ✅ Lưu lịch sử chỉnh sửa câu hỏi

**Models:**
- `Question` - Câu hỏi
- `QuestionFolder` - Thư mục câu hỏi
- `QuestionEditHistory` - Lịch sử chỉnh sửa

**Services:**
- `QuestionBankService` - CRUD câu hỏi, lọc, tìm kiếm
- `QuestionImportService` - Import câu hỏi từ file

---

### 🎓 StudentPractice - Sử dụng câu hỏi để luyện tập
StudentPractice chỉ **sử dụng** câu hỏi đã được phê duyệt từ QuestionBank:
- ✅ Cho học sinh chọn chế độ luyện tập (theo môn/chủ đề/hỗn hợp)
- ✅ Lấy câu hỏi từ QuestionBank (status = 'approved')
- ✅ Tạo bài luyện tập với các câu hỏi đã chọn
- ✅ Lưu câu trả lời của học sinh
- ✅ Chấm điểm tự động (dựa trên đáp án từ QuestionBank)
- ✅ Lưu lịch sử luyện tập
- ✅ Thống kê kết quả học sinh

**Models:**
- `PracticeAttempt` - Một bài luyện tập
- `PracticeAnswer` - Câu trả lời của học sinh cho một câu hỏi

**Services:**
- `PracticeService` - Quản lý bài luyện tập, chấm điểm, lưu lịch sử

---

## 🔄 Luồng công việc

### 1️⃣ Tạo bài luyện tập
```
Học sinh
  ↓
PracticeController::start()
  ↓
PracticeService::startPractice()
  ↓
Lấy câu hỏi từ QuestionBank (where status = 'approved')
  ↓
Tạo PracticeAttempt + PracticeAnswer records
  ↓
Chuyển hướng đến form làm bài
```

### 2️⃣ Học sinh làm bài
```
Học sinh trả lời câu hỏi
  ↓
PracticeController::submitAnswer()
  ↓
PracticeService::submitAnswer()
  ↓
Lấy đáp án đúng từ Question model (QuestionBank)
  ↓
So sánh và chấm điểm
  ↓
Lưu PracticeAnswer
  ↓
Trả về JSON response (success/fail)
```

### 3️⃣ Hoàn thành bài luyện tập
```
Học sinh nộp bài
  ↓
PracticeController::complete()
  ↓
PracticeService::completePractice()
  ↓
Tính toán điểm cuối cùng
  ↓
Cập nhật PracticeAttempt (completed_at, score)
  ↓
Chuyển hướng đến kết quả
```

### 4️⃣ Xem kết quả
```
Học sinh xem kết quả
  ↓
PracticeController::result()
  ↓
PracticeService::getPracticeDetails()
  ↓
Trả về câu trả lời + câu hỏi + kết quả chấm điểm
```

---

## 📊 Cơ sở dữ liệu

### QuestionBank Tables
```sql
-- Câu hỏi
question_bank_questions
  - id, created_by, reviewed_by, folder_id
  - subject, topic, type, difficulty, status
  - content, options, correct_answers (đáp án chính xác)
  - explanation, tags, review_note, reviewed_at
  - created_at, updated_at, deleted_at

-- Thư mục
question_bank_folders
  - id, created_by, name, subject, description, color
  - created_at, updated_at, deleted_at

-- Lịch sử chỉnh sửa
question_bank_edit_histories
  - id, question_id, user_id, action, old_values, new_values
  - created_at
```

### StudentPractice Tables
```sql
-- Bài luyện tập
student_practice_attempts
  - id, student_id (FK → users)
  - mode, subject, topic, difficulty
  - total_questions, correct_answers, score
  - started_at, completed_at
  - created_at, updated_at
  - INDEX: (student_id, completed_at)

-- Câu trả lời của học sinh
student_practice_answers
  - id, attempt_id (FK → student_practice_attempts)
  - question_id (FK → question_bank_questions)
  - student_answer (JSON), is_correct, points
  - created_at, updated_at
  - UNIQUE: (attempt_id, question_id)
```

---

## 🔗 Mối quan hệ

```
QuestionBank (chính)
  ↓ (provides approved questions)
  ↓
StudentPractice (sử dụng)

Question (QuestionBank)
  ← many-to-many through PracticeAnswer ← many (StudentPractice)

PracticeAttempt
  - has many: PracticeAnswer
  - belongs to: User (student)

PracticeAnswer
  - belongs to: PracticeAttempt
  - belongs to: Question (from QuestionBank)
```

---

## 📝 Chấm điểm tự động

StudentPractice hỗ trợ chấm điểm tự động cho các loại câu hỏi:

### ✅ Được hỗ trợ (tự động)
- **Single Choice** - So sánh với một đáp án đúng
- **Multiple Choice** - So sánh với nhiều đáp án đúng
- **True/False** - So sánh với đáp án đúng
- **Short Answer** - So sánh theo chuỗi (không phân biệt hoa/thường)

### ⚠️ Không hỗ trợ tự động
- **Essay** - Cần chấm điểm thủ công

---

## 📊 Thống kê và Lịch sử

### Xem lịch sử
```php
$history = $practiceService->getStudentHistory($user);
// Trả về danh sách các bài luyện tập đã hoàn thành
```

### Xem thống kê
```php
$stats = $practiceService->getStudentStats($user);
// [
//     'total_attempts' => số lần luyện tập,
//     'total_questions' => tổng câu hỏi,
//     'total_correct' => tổng câu đúng,
//     'average_score' => điểm trung bình,
//     'completion_rate' => tỉ lệ hoàn thành,
// ]
```

---

## 🚀 Cài đặt

### 1. Publish Migration
```bash
php artisan migrate --path=packages/Students/StudentPractice/database/migrations
```

### Hoặc sử dụng publish tag
```bash
php artisan vendor:publish --tag=student-practice-migrations
php artisan migrate
```

### 2. Đảm bảo QuestionBank đã được cài đặt
StudentPractice cần QuestionBank để hoạt động. Kiểm tra `composer.json`:
```json
{
    "require": {
        "mindigo/question-bank": "*"
    }
}
```

---

## 💡 Ví dụ sử dụng

### Bắt đầu luyện tập
```php
$attemptData = [
    'mode' => 'subject', // 'subject', 'topic', 'mixed'
    'subject' => 'Toán học',
    'difficulty' => 'medium', // 'easy', 'medium', 'hard', null
];

$attempt = $practiceService->startPractice($user, $attemptData);
// Tạo PracticeAttempt + PracticeAnswer records
```

### Gửi câu trả lời
```php
$answer = ['choice' => 'A']; // Tùy loại câu hỏi

$practiceAnswer = $practiceService->submitAnswer(
    $attempt,
    $questionId,
    $answer
);
// is_correct được tính tự động dựa trên Question model
```

### Hoàn thành bài luyện tập
```php
$completedAttempt = $practiceService->completePractice($attempt);
// score = (correct_answers / total_questions) * 100
```

---

## ✅ Tại sao phân chia như vậy?

### 🎯 Lợi ích
1. **Không trùng lặp dữ liệu** - Câu hỏi được lưu ở một nơi duy nhất
2. **Dễ bảo trì** - Thay đổi câu hỏi không ảnh hưởng đến bài luyện tập cũ
3. **Quyền kiểm soát** - QuestionBank quản lý độ chính xác của câu hỏi
4. **Mở rộng dễ dàng** - Thêm tính năng luyện tập không ảnh hưởng QuestionBank
5. **Kiểm toán tốt** - Lịch sử luyện tập độc lập với lịch sử chỉnh sửa câu hỏi

### 🔒 Ràng buộc
- StudentPractice chỉ có thể **đọc** Question, không được **ghi**
- StudentPractice chỉ sử dụng Question có `status = 'approved'`
- StudentPractice không được thay đổi đáp án của Question
- Xóa Question sẽ bảo vệ PracticeAnswer (onDelete='restrict')

---

## 🐛 Debugging

### Kiểm tra câu hỏi được lấy
```php
$questions = Question::where('status', 'approved')
    ->where('subject', 'Toán')
    ->count();
```

### Kiểm tra bài luyện tập
```php
$attempt = PracticeAttempt::with(['answers.question'])->find($id);
$attempt->load('answers');
```

### Kiểm tra chấm điểm
```php
$answer = PracticeAnswer::find($id);
$answer->question; // Question từ QuestionBank
$answer->is_correct; // Kết quả chấm điểm
```
