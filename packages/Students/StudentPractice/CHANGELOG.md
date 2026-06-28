# StudentPractice Package - Cập nhật phiên bản

## 📌 Tóm tắt thay đổi

StudentPractice đã được cấu trúc lại để có **sự phụ thuộc rõ ràng với QuestionBank** làm nguồn dữ liệu câu hỏi duy nhất.

---

## 🎯 Mục đích

- **QuestionBank** là chủ sở hữu duy nhất của dữ liệu câu hỏi
- **StudentPractice** chỉ sử dụng và không sửa đổi câu hỏi
- Tránh trùng lặp dữ liệu
- Dễ bảo trì và mở rộng tính năng

---

## 📁 Cấu trúc tệp mới

```
packages/Students/StudentPractice/
├── src/
│   ├── Models/
│   │   ├── PracticeAttempt.php        (🆕 Bài luyện tập)
│   │   └── PracticeAnswer.php         (🆕 Câu trả lời)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── PracticeController.php (✏️ Cập nhật)
│   │   └── Requests/
│   │       ├── StartPracticeRequest.php    (✏️ Cập nhật)
│   │       └── SubmitAnswerRequest.php     (🆕 Mới)
│   ├── Services/
│   │   └── PracticeService.php        (✏️ Cập nhật - thêm nhiều phương thức)
│   ├── Providers/
│   │   └── StudentPracticeServiceProvider.php (✏️ Cập nhật)
│   └── routes/
│       └── web.php                    (✏️ Cập nhật - thêm route mới)
├── database/
│   └── migrations/                    (🆕 Thư mục mới)
│       ├── 2026_06_28_000001_create_practice_attempts_table.php
│       └── 2026_06_28_000002_create_practice_answers_table.php
├── DEPENDENCY_GUIDE.md                (🆕 Hướng dẫn)
└── CHANGELOG.md                       (🆕 Bản nhật ký)
```

---

## ✨ Các tính năng mới

### 1. **Models mới**
- `PracticeAttempt` - Lưu trữ bài luyện tập
- `PracticeAnswer` - Lưu trữ câu trả lời của học sinh

### 2. **Phương thức mới trong PracticeService**

#### Quản lý bài luyện tập
```php
// Bắt đầu bài luyện tập
$attempt = $service->startPractice($user, [
    'mode' => 'subject', // 'subject', 'topic', 'mixed'
    'subject' => 'Toán học',
    'difficulty' => 'medium',
]);

// Gửi câu trả lời
$answer = $service->submitAnswer($attempt, $questionId, [
    'choice' => 'A'
]);

// Hoàn thành bài luyện tập
$completed = $service->completePractice($attempt);
```

#### Lịch sử và thống kê
```php
// Lấy lịch sử luyện tập
$history = $service->getStudentHistory($user, $limit = 10);

// Lấy thống kê
$stats = $service->getStudentStats($user);
// ['total_attempts', 'total_questions', 'total_correct', 
//  'average_score', 'completion_rate']

// Lấy chi tiết bài luyện tập
$details = $service->getPracticeDetails($attempt);
```

### 3. **Controller mới**
- `start()` - Bắt đầu bài luyện tập
- `attempt()` - Xem bài luyện tập đang làm
- `submitAnswer()` - Gửi câu trả lời
- `complete()` - Hoàn thành bài luyện tập
- `result()` - Xem kết quả
- `history()` - Xem lịch sử

### 4. **Routes mới**
```
POST   /student/practice/start              -> start practice
GET    /student/practice/{id}/attempt       -> view attempt form
POST   /student/practice/{id}/submit-answer -> submit answer
POST   /student/practice/{id}/complete      -> complete practice
GET    /student/practice/{id}/result        -> view result
GET    /student/practice/history            -> view history
```

---

## 🔧 Chấm điểm tự động

StudentPractice hỗ trợ chấm điểm tự động cho:

| Loại câu hỏi | Hỗ trợ | Ghi chú |
|-------------|--------|--------|
| Single Choice | ✅ | So sánh với 1 đáp án |
| Multiple Choice | ✅ | So sánh với nhiều đáp án |
| True/False | ✅ | So sánh boolean |
| Short Answer | ✅ | So sánh chuỗi (ignore case) |
| Essay | ❌ | Cần chấm thủ công |

---

## 📊 Cơ sở dữ liệu

### Migration cần chạy
```bash
php artisan migrate
```

### Bảng mới tạo
1. **student_practice_attempts** - Bài luyện tập
2. **student_practice_answers** - Câu trả lời

---

## 🔗 Phụ thuộc

### Composer requirements
```json
{
    "require": {
        "mindigo/question-bank": "*"
    }
}
```

### Mối quan hệ
```
PracticeAttempt ← (1:N) → PracticeAnswer → (N:1) → Question (QuestionBank)
```

---

## 📝 Phương pháp sử dụng

### Khởi tạo
```php
$service = app(PracticeService::class);
$user = auth()->user();
```

### Bắt đầu luyện tập
```php
$attempt = $service->startPractice($user, [
    'mode' => 'subject',
    'subject' => 'Toán',
    'topic' => 'Đại số',
    'difficulty' => 'medium',
]);
```

### Trả lời câu hỏi
```php
// Loại Single Choice
$service->submitAnswer($attempt, $questionId, ['choice' => 'A']);

// Loại Multiple Choice
$service->submitAnswer($attempt, $questionId, ['choices' => ['A', 'C']]);

// Loại True/False
$service->submitAnswer($attempt, $questionId, ['answer' => true]);

// Loại Short Answer
$service->submitAnswer($attempt, $questionId, ['text' => 'Toán học']);
```

### Hoàn thành và xem kết quả
```php
$completed = $service->completePractice($attempt);
$details = $service->getPracticeDetails($completed);
```

---

## 🧪 Kiểm tra

### Unit Tests (cần thêm)
```bash
php artisan test --filter=PracticeServiceTest
```

### Integration Tests (cần thêm)
```bash
php artisan test --filter=PracticeControllerTest
```

---

## ⚠️ Breaking Changes

Nếu bạn đã sử dụng StudentPractice trước đây, hãy cập nhật:

### Trước (cũ)
```php
$formData = $service->formData();
```

### Sau (mới)
```php
$formData = $service->formData(auth()->user());
```

---

## 🚀 Cài đặt/Nâng cấp

### Lần đầu tiên
1. Cập nhật composer: `composer update`
2. Publish migrations: `php artisan vendor:publish --tag=student-practice-migrations`
3. Chạy migration: `php artisan migrate`

### Nâng cấp từ phiên bản cũ
1. Backup database
2. Chạy migrations mới: `php artisan migrate`
3. Kiểm tra Controller để truyền `$user` vào `formData()`

---

## 📚 Tài liệu

- [DEPENDENCY_GUIDE.md](DEPENDENCY_GUIDE.md) - Hướng dẫn chi tiết về phụ thuộc
- [API Reference](#) - (sẽ thêm)
- [Examples](#) - (sẽ thêm)

---

## 🐛 Troubleshooting

### Lỗi: "Class PracticeAttempt not found"
```bash
# Chạy autoload
composer dump-autoload

# Hoặc cập nhật
composer update
```

### Lỗi: Migration không chạy
```bash
# Publish migrations
php artisan vendor:publish --tag=student-practice-migrations

# Chạy migration
php artisan migrate
```

### Lỗi: "Undefined method formData"
Kiểm tra Controller truyền `auth()->user()` vào `formData()`:
```php
$formData = $this->service->formData(auth()->user());
```

---

## 📞 Hỗ trợ

Nếu gặp vấn đề:
1. Xem [DEPENDENCY_GUIDE.md](DEPENDENCY_GUIDE.md)
2. Kiểm tra [CHANGELOG.md](CHANGELOG.md)
3. Chạy `php artisan migrate --refresh --seed`

---

## ✅ Danh sách kiểm tra

- [ ] Cập nhật composer
- [ ] Chạy migrations
- [ ] Cập nhật Controllers (truyền `$user`)
- [ ] Publish views (nếu cần)
- [ ] Test bài luyện tập
- [ ] Kiểm tra chấm điểm
- [ ] Xem lịch sử và thống kê

---

**Phiên bản:** 2.0.0  
**Ngày cập nhật:** 2026-06-28  
**Trạng thái:** ✅ Sản xuất
