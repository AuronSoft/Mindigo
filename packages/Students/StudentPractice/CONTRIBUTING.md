# StudentPractice - Hướng dẫn phát triển

## 📚 Tài liệu này dành cho các nhà phát triển muốn mở rộng StudentPractice

---

## 🏗️ Kiến trúc

```
StudentPractice (Consumer)
    ↓ (phụ thuộc vào)
QuestionBank (Provider)

StudentPractice:
  - Sử dụng Question từ QuestionBank
  - Không được sửa đổi Question
  - Chỉ tạo PracticeAttempt + PracticeAnswer
  - Độc lập với lifecycle của Question
```

---

## 📂 Cấu trúc thư mục

```
src/
├── Contracts/
│   └── PracticeServiceInterface.php      # Interface của Service
├── Models/
│   ├── PracticeAttempt.php               # Bài luyện tập
│   └── PracticeAnswer.php                # Câu trả lời
├── Services/
│   └── PracticeService.php               # Logic chính
├── Http/
│   ├── Controllers/
│   │   └── PracticeController.php        # Request/Response handling
│   └── Requests/
│       ├── StartPracticeRequest.php      # Validation cho start
│       └── SubmitAnswerRequest.php       # Validation cho submit answer
├── Providers/
│   └── StudentPracticeServiceProvider.php # Bootstrap
└── routes/
    └── web.php                            # Routes

database/
└── migrations/
    ├── 2026_06_28_000001_create_practice_attempts_table.php
    └── 2026_06_28_000002_create_practice_answers_table.php
```

---

## 🔄 Data Flow

### Bắt đầu luyện tập
```
Request
  ↓
PracticeController::start()
  ↓
StartPracticeRequest::validate()
  ↓
PracticeService::startPractice()
  ├─ getQuestionsForPractice() ← lấy từ QuestionBank
  ├─ PracticeAttempt::create()
  └─ PracticeAnswer::create() (nhiều record)
  ↓
Response: redirect to attempt form
```

### Gửi câu trả lời
```
AJAX Request
  ↓
PracticeController::submitAnswer()
  ↓
SubmitAnswerRequest::validate()
  ↓
PracticeService::submitAnswer()
  ├─ getQuestion() ← lấy từ QuestionBank
  ├─ gradeAnswer() ← chấm điểm
  └─ PracticeAnswer::update()
  ↓
Response: JSON { success: true, isCorrect: boolean }
```

### Hoàn thành bài luyện tập
```
Request
  ↓
PracticeController::complete()
  ↓
PracticeService::completePractice()
  ├─ Đếm correct answers
  ├─ Tính score
  └─ PracticeAttempt::update()
  ↓
Response: redirect to result page
```

---

## 🧩 Mối quan hệ Model

```
User (Auth)
  ↓ 1:N
PracticeAttempt
  ↓ 1:N
PracticeAnswer ← N:1 → Question (QuestionBank)
```

### Relationships
```php
// User → PracticeAttempt
User::hasMany('practiceAttempts', 'student_id')

// PracticeAttempt → PracticeAnswer
PracticeAttempt::hasMany('answers')

// PracticeAnswer → Question
PracticeAnswer::belongsTo('question', 'question_id')
```

---

## 📋 Mở rộng: Thêm tính năng mới

### 1. Thêm Report/Analysis

```php
// Report.php (Model)
class PracticeReport extends Model
{
    public function attempt() { ... }
}

// ReportService.php
class ReportService
{
    public function generateReport(PracticeAttempt $attempt)
    {
        // Phân tích chi tiết
    }
}
```

### 2. Thêm Gamification

```php
// Achievement.php (Model)
class Achievement extends Model
{
    // Badges, points, streaks, ...
}

// GamificationService.php
class GamificationService
{
    public function awardPoints(PracticeAttempt $attempt)
    {
        // Logic thưởng điểm
    }
}
```

### 3. Thêm Adaptive Learning

```php
// AdaptiveService.php
class AdaptiveService
{
    public function getNextDifficulty(User $student)
    {
        // Dựa trên performance, recommend difficulty
    }
}
```

### 4. Thêm Collaborative Features

```php
// GroupPractice.php (Model)
class GroupPractice extends Model
{
    public function students() { ... }
    public function attempts() { ... }
}
```

---

## ✅ Best Practices

### 1. **Không sửa đổi Question**
```php
// ❌ KHÔNG làm điều này
$question->correct_answers = $newAnswers;
$question->save();

// ✅ Làm thế này
$answer = PracticeAnswer::create([
    'question_id' => $question->id,
    'student_answer' => $studentAnswer,
    'is_correct' => $this->gradeAnswer($question, $studentAnswer),
]);
```

### 2. **Kiểm tra Question status**
```php
// ✅ Luôn kiểm tra status
$question = Question::where('status', 'approved')->find($id);
if (!$question) {
    throw new Exception('Question not available for practice');
}
```

### 3. **Tách biệt Logic**
```php
// ✅ Tách business logic vào Service
// PracticeService.php
private function gradeAnswer(Question $question, array $answer): bool
{
    // chấm điểm logic
}

// ❌ Không để logic ở Controller
// PracticeController.php
public function submitAnswer() {
    // $isCorrect = /* chấm điểm logic */; // SAI
}
```

### 4. **Validate Input**
```php
// ✅ Sử dụng FormRequest
class StartPracticeRequest extends FormRequest
{
    public function rules() { ... }
}

// ❌ Không validate ở Service
class PracticeService
{
    public function startPractice(User $student, array $data) {
        // if (empty($data['mode'])) { ... } // SAI
    }
}
```

### 5. **Transaction cho consistency**
```php
// ✅ Sử dụng transaction cho multiple updates
DB::transaction(function () {
    $attempt->update([...]);
    foreach ($answers as $answer) {
        $answer->update([...]);
    }
});
```

---

## 🧪 Testing

### Unit Tests
```php
// tests/Unit/Services/PracticeServiceTest.php
class PracticeServiceTest extends TestCase
{
    public function test_start_practice_creates_attempt()
    {
        $user = User::factory()->create();
        $service = app(PracticeService::class);
        
        $attempt = $service->startPractice($user, [
            'mode' => 'subject',
            'subject' => 'Toán',
        ]);
        
        $this->assertNotNull($attempt->id);
        $this->assertEquals($user->id, $attempt->student_id);
    }
}
```

### Feature Tests
```php
// tests/Feature/PracticeTest.php
class PracticeTest extends TestCase
{
    public function test_student_can_start_practice()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $response = $this->actingAs($user)
            ->post('/student/practice/start', [
                'mode' => 'subject',
                'subject' => 'Toán',
            ]);
        
        $response->assertRedirect('/student/practice/*/attempt');
    }
}
```

---

## 🔐 Security Considerations

### 1. **Authorization**
```php
// ✅ Kiểm tra quyền sở hữu
public function result($id)
{
    $attempt = PracticeAttempt::findOrFail($id);
    abort_if($attempt->student_id !== auth()->id(), 403);
}

// ❌ Không bỏ qua check này
public function result($id)
{
    $attempt = PracticeAttempt::findOrFail($id);
    // Nếu không check, user khác có thể xem result của người khác
}
```

### 2. **Input Validation**
```php
// ✅ Validate tất cả input
class SubmitAnswerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'question_id' => 'required|integer|exists:question_bank_questions,id',
            'answer' => 'required|array',
        ];
    }
}

// ❌ Không skip validation
public function submitAnswer(Request $request)
{
    $questionId = $request->input('question_id'); // Có thể là string, object, etc
}
```

### 3. **Rate Limiting**
```php
// ✅ Thêm rate limiting
Route::post('/submit-answer', [PracticeController::class, 'submitAnswer'])
    ->middleware('throttle:30,1'); // 30 requests per minute
```

---

## 📊 Performance Optimization

### 1. **Eager Loading**
```php
// ✅ Eager load relationships
$attempts = PracticeAttempt::with(['answers.question', 'student'])
    ->where('student_id', $user->id)
    ->get();

// ❌ N+1 Query Problem
$attempts = PracticeAttempt::all();
foreach ($attempts as $attempt) {
    $attempt->answers; // Query for each attempt
    $attempt->student; // Query for each attempt
}
```

### 2. **Indexing**
```php
// ✅ Thêm index vào database
Schema::create('student_practice_attempts', function (Blueprint $table) {
    $table->index(['student_id', 'completed_at']);
    $table->index('status');
});

// Phút toàn bộ bảng:
SELECT * FROM student_practice_attempts 
WHERE student_id = 1 AND completed_at IS NOT NULL
ORDER BY completed_at DESC;
```

### 3. **Caching**
```php
// ✅ Cache form data (thay đổi không thường xuyên)
public function formData(User $user): array
{
    return Cache::remember("practice_form_data_{$user->id}", 3600, function () {
        return [
            'subjects' => $this->questionBank->subjects(),
            'subjectTopics' => $this->questionBank->topicsBySubject(),
        ];
    });
}
```

---

## 🐛 Debugging Tips

### 1. Enable Query Logging
```php
// config/app.php hoặc .env
DB::enableQueryLog();
$results = PracticeAttempt::with('answers.question')->get();
dd(DB::getQueryLog());
```

### 2. Debug Models
```php
$attempt = PracticeAttempt::find($id);
dd([
    'attempt' => $attempt,
    'answers' => $attempt->answers,
    'questions' => $attempt->answers->map->question,
]);
```

### 3. Test Grading Logic
```php
$question = Question::find($id);
$answer = ['choice' => 'A'];
$isCorrect = $this->gradeAnswer($question, $answer);
dd(['question' => $question, 'answer' => $answer, 'isCorrect' => $isCorrect]);
```

---

## 📝 Changelog

### v2.0.0 (2026-06-28)
- ✨ Thêm Models: PracticeAttempt, PracticeAnswer
- ✨ Thêm tính năng: Chấm điểm, Lịch sử, Thống kê
- ✨ Thêm Routes mới
- 🔧 Refactor PracticeService
- 📚 Thêm tài liệu chi tiết

### v1.0.0 (Earlier)
- Initial release

---

## 🤝 Contributing

Khi thêm tính năng mới:

1. **Tạo branch**: `feature/new-feature`
2. **Viết tests**: Unit + Feature tests
3. **Update documentation**
4. **Submit PR**

---

## 📞 Support

- Xem [DEPENDENCY_GUIDE.md](DEPENDENCY_GUIDE.md)
- Xem [README.md](README.md)
- Xem [CHANGELOG.md](CHANGELOG.md)

---

**Last Updated:** 2026-06-28
