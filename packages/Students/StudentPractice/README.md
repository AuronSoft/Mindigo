# StudentPractice Package

Módulo dành cho học sinh thực hành các câu hỏi từ **QuestionBank**.

## ✨ Tính năng

- 📚 Luyện tập với câu hỏi từ QuestionBank
- 🎯 Chọn chế độ luyện tập (theo môn/chủ đề/hỗn hợp)
- 📊 Chấm điểm tự động
- 💾 Lưu lịch sử luyện tập
- 📈 Xem thống kê kết quả

## 🔗 Phụ thuộc

StudentPractice **phụ thuộc vào** [mindigo/question-bank](../Mindigo/QuestionBank) để lấy câu hỏi.

```json
{
    "require": {
        "mindigo/question-bank": "*"
    }
}
```

## 📦 Cài đặt

### 1. Thêm package vào composer.json (nếu chưa có)
```bash
composer require mindigo/student-practice
```

### 2. Publish migrations
```bash
php artisan vendor:publish --tag=student-practice-migrations
```

### 3. Chạy migration
```bash
php artisan migrate
```

### 4. (Tùy chọn) Publish views và language files
```bash
# Views
php artisan vendor:publish --tag=student-practice-views

# Language files
php artisan vendor:publish --tag=student-practice-lang
```

## 🚀 Sử dụng

### 1. Bắt đầu bài luyện tập

```php
use Mindigo\StudentPractice\Services\PracticeService;

$service = app(PracticeService::class);
$user = auth()->user();

// Bắt đầu luyện tập
$attempt = $service->startPractice($user, [
    'mode' => 'subject', // 'subject', 'topic', 'mixed'
    'subject' => 'Toán',
    'difficulty' => 'medium', // optional
]);
```

### 2. Gửi câu trả lời

```php
// Single Choice
$service->submitAnswer($attempt, $questionId, ['choice' => 'A']);

// Multiple Choice
$service->submitAnswer($attempt, $questionId, ['choices' => ['A', 'B']]);

// True/False
$service->submitAnswer($attempt, $questionId, ['answer' => true]);

// Short Answer
$service->submitAnswer($attempt, $questionId, ['text' => 'câu trả lời']);
```

### 3. Hoàn thành bài luyện tập

```php
$completed = $service->completePractice($attempt);
```

### 4. Xem kết quả

```php
$details = $service->getPracticeDetails($completed);

// $details = [
//     'attempt' => $attempt,
//     'answers' => $answers,
//     'summary' => [
//         'total_questions' => 10,
//         'correct_answers' => 8,
//         'score' => 80.0,
//         'duration' => 15, // phút
//     ]
// ]
```

### 5. Lịch sử và thống kê

```php
// Lịch sử
$history = $service->getStudentHistory($user);

// Thống kê
$stats = $service->getStudentStats($user);

// $stats = [
//     'total_attempts' => 5,
//     'total_questions' => 50,
//     'total_correct' => 42,
//     'average_score' => 84.0,
//     'completion_rate' => 84.0,
// ]
```

## 🛣️ Routes

```
GET    /student/practice/                          # Danh sách câu hỏi
POST   /student/practice/start                     # Bắt đầu luyện tập
GET    /student/practice/history                   # Lịch sử
GET    /student/practice/{id}                      # Chi tiết câu hỏi
GET    /student/practice/{id}/attempt              # Form làm bài
POST   /student/practice/{id}/submit-answer        # Gửi câu trả lời
POST   /student/practice/{id}/complete             # Hoàn thành
GET    /student/practice/{id}/result               # Xem kết quả
```

## 📋 Models

### PracticeAttempt
Biểu diễn một bài luyện tập

```php
$attempt->student;           // Học sinh
$attempt->answers;           // Các câu trả lời
$attempt->total_questions;   // Tổng câu hỏi
$attempt->correct_answers;   // Số câu đúng
$attempt->score;             // Điểm (%)
$attempt->duration_in_minutes; // Thời gian làm bài
$attempt->isCompleted();      // Đã hoàn thành?
```

### PracticeAnswer
Biểu diễn câu trả lời của học sinh

```php
$answer->attempt;       // Bài luyện tập
$answer->question;      // Câu hỏi (từ QuestionBank)
$answer->student_answer; // Câu trả lời của học sinh
$answer->is_correct;    // Câu trả lời có đúng?
$answer->points;        // Điểm cho câu này
```

## 🎯 Chấm điểm

StudentPractice hỗ trợ chấm điểm tự động cho:

| Loại câu hỏi | Hỗ trợ | Cách so sánh |
|-------------|--------|------------|
| Single Choice | ✅ | Khớp với 1 đáp án |
| Multiple Choice | ✅ | Khớp với tất cả đáp án |
| True/False | ✅ | Khớp giá trị boolean |
| Short Answer | ✅ | So sánh chuỗi (ignore case) |
| Essay | ❌ | Cần chấm thủ công |

## 📚 Tài liệu chi tiết

- [DEPENDENCY_GUIDE.md](DEPENDENCY_GUIDE.md) - Phụ thuộc với QuestionBank
- [CHANGELOG.md](CHANGELOG.md) - Nhật ký cập nhật

## 🔐 Quyền

StudentPractice yêu cầu role `student` hoặc `admin`:

```php
Route::middleware(['auth', 'role:student|admin'])->group(function () {
    // ...
});
```

## 📝 Ví dụ

### Controller
```php
class PracticeController extends Controller
{
    public function __construct(protected PracticeService $service)
    {
    }

    public function index()
    {
        $formData = $this->service->formData(auth()->user());
        return view('practice.index', compact('formData'));
    }

    public function start(StartPracticeRequest $request)
    {
        $attempt = $this->service->startPractice(
            auth()->user(),
            $request->validated()
        );
        return redirect()->route('student.practice.attempt', $attempt->id);
    }
}
```

### Blade Template
```blade
@forelse($attempt->answers as $answer)
    <div class="question">
        <h4>{{ $answer->question->content }}</h4>
        
        @if($answer->question->type === 'single_choice')
            <div class="options">
                @foreach($answer->question->options as $key => $option)
                    <label>
                        <input type="radio" 
                               name="answer" 
                               value="{{ $key }}"
                               {{ $answer->student_answer['choice'] === $key ? 'checked' : '' }}>
                        {{ $option }}
                    </label>
                @endforeach
            </div>
        @endif

        <div class="result">
            @if($answer->is_correct)
                <span class="badge success">✓ Đúng</span>
            @else
                <span class="badge danger">✗ Sai</span>
            @endif
        </div>
    </div>
@empty
    <p>Không có câu hỏi nào</p>
@endforelse
```

## 🐛 Troubleshooting

### Câu hỏi không hiển thị
- Kiểm tra QuestionBank có câu hỏi với `status = 'approved'`
- Kiểm tra filter (subject, topic, difficulty) khớp với câu hỏi

### Chấm điểm không chính xác
- Kiểm tra định dạng câu trả lời
- Kiểm tra `correct_answers` trong Question

### Lỗi Foreign Key
- Đảm bảo QuestionBank đã được cài đặt
- Chạy migrations của QuestionBank trước

## 📄 License

MIT

## 👥 Tác giả

Mindigo Team
