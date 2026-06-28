<?php

namespace Mindigo\StudentPractice\Services;

use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\QuestionBank\Services\QuestionBankService;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Models\PracticeAnswer;

class PracticeService
{
    public function __construct(protected QuestionBankService $questionBank)
    {
    }

    /**
     * Lấy danh sách câu hỏi approved để luyện tập theo filter.
     * Câu hỏi được lấy từ QuestionBank.
     */
    public function getQuestions(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Question::query()
            ->where('status', 'approved')
            ->latest('updated_at');

        foreach (['subject', 'topic', 'type', 'difficulty'] as $filter) {
            if (filled($filters[$filter] ?? null)) {
                $query->where($filter, $filters[$filter]);
            }
        }

        if (filled($filters['keyword'] ?? null)) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($builder) use ($keyword) {
                $builder->where('content', 'like', "%{$keyword}%")
                    ->orWhere('subject', 'like', "%{$keyword}%")
                    ->orWhere('topic', 'like', "%{$keyword}%");
            });
        }

        return $query->paginate(10)->withQueryString();
    }

    /**
     * Lấy một câu hỏi theo ID (chỉ approved).
     * Câu hỏi được lấy từ QuestionBank.
     */
    public function getQuestion(int $id): ?Question
    {
        return Question::query()
            ->where('status', 'approved')
            ->find($id);
    }

    /**
     * Dữ liệu cho form filter / bắt đầu luyện tập.
     */
    public function formData(User $user): array
    {
        return [
            'types'       => Question::TYPES,
            'difficulties' => Question::DIFFICULTIES,
            'subjects'    => $this->questionBank->subjects(),
            'subjectTopics' => $this->questionBank->topicsBySubject(),
        ];
    }

    /**
     * Bắt đầu một bài luyện tập mới.
     * Các câu hỏi được lấy từ QuestionBank với các filter.
     */
    public function startPractice(User $student, array $data): PracticeAttempt
    {
        // Lấy câu hỏi từ QuestionBank dựa trên filter
        $questions = $this->getQuestionsForPractice($data);

        if ($questions->isEmpty()) {
            throw new \Exception('No approved questions found matching your filters.');
        }

        // Tạo bài luyện tập
        $attempt = PracticeAttempt::create([
            'student_id' => $student->id,
            'mode' => $data['mode'] ?? 'mixed',
            'subject' => $data['subject'] ?? null,
            'topic' => $data['topic'] ?? null,
            'difficulty' => $data['difficulty'] ?? null,
            'total_questions' => $questions->count(),
            'correct_answers' => 0,
            'started_at' => now(),
        ]);

        // Lưu các câu hỏi được chọn
        foreach ($questions as $question) {
            PracticeAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'student_answer' => null,
                'is_correct' => false,
                'points' => 0,
            ]);
        }

        return $attempt;
    }

    /**
     * Lấy câu hỏi từ QuestionBank dựa trên các filter.
     */
    private function getQuestionsForPractice(array $data): \Illuminate\Database\Eloquent\Collection
    {
        $query = Question::query()
            ->where('status', 'approved');

        if (filled($data['subject'] ?? null)) {
            $query->where('subject', $data['subject']);
        }

        if (filled($data['topic'] ?? null)) {
            $query->where('topic', $data['topic']);
        }

        if (filled($data['difficulty'] ?? null)) {
            $query->where('difficulty', $data['difficulty']);
        }

        return $query->get();
    }

    /**
     * Lưu câu trả lời của học sinh.
     */
    public function submitAnswer(PracticeAttempt $attempt, int $questionId, array $answer): PracticeAnswer
    {
        $question = $this->getQuestion($questionId);
        
        if (!$question) {
            throw new \Exception('Question not found or not approved.');
        }

        $practiceAnswer = PracticeAnswer::where('attempt_id', $attempt->id)
            ->where('question_id', $questionId)
            ->first();

        if (!$practiceAnswer) {
            throw new \Exception('This question is not part of this practice attempt.');
        }

        // Chấm điểm câu trả lời
        $isCorrect = $this->gradeAnswer($question, $answer);

        $practiceAnswer->update([
            'student_answer' => $answer,
            'is_correct' => $isCorrect,
            'points' => $isCorrect ? 1 : 0,
        ]);

        return $practiceAnswer;
    }

    /**
     * Chấm điểm một câu trả lời.
     */
    private function gradeAnswer(Question $question, array $answer): bool
    {
        // Lấy đáp án đúng từ Question (được quản lý bởi QuestionBank)
        $correctAnswers = $question->correct_answers;

        return match ($question->type) {
            'single_choice' => isset($answer['choice']) && $answer['choice'] == $correctAnswers[0] ?? false,
            'multiple_choice' => isset($answer['choices']) && count(array_intersect($answer['choices'], $correctAnswers)) === count($correctAnswers),
            'true_false' => isset($answer['answer']) && $answer['answer'] == $correctAnswers[0] ?? false,
            'short_answer' => $this->matchShortAnswer($answer['text'] ?? '', $correctAnswers),
            'essay' => false, // Essay answers require manual grading
            default => false,
        };
    }

    /**
     * So sánh câu trả lời ngắn (không phân biệt hoa/thường).
     */
    private function matchShortAnswer(string $studentAnswer, array $correctAnswers): bool
    {
        $studentAnswer = strtolower(trim($studentAnswer));

        foreach ($correctAnswers as $correct) {
            if (strtolower(trim((string) $correct)) === $studentAnswer) {
                return true;
            }
        }

        return false;
    }

    /**
     * Hoàn thành bài luyện tập và tính điểm.
     */
    public function completePractice(PracticeAttempt $attempt): PracticeAttempt
    {
        // Đếm số câu trả lời đúng
        $correctAnswers = $attempt->answers()->where('is_correct', true)->count();

        // Cập nhật bài luyện tập
        $attempt->update([
            'correct_answers' => $correctAnswers,
            'completed_at' => now(),
            'score' => $attempt->calculateScore(),
        ]);

        return $attempt->fresh();
    }

    /**
     * Lấy lịch sử luyện tập của học sinh.
     */
    public function getStudentHistory(User $student, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return PracticeAttempt::where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy thống kê luyện tập của học sinh.
     */
    public function getStudentStats(User $student): array
    {
        $completedAttempts = PracticeAttempt::where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->get();

        $totalAttempts = $completedAttempts->count();
        $totalCorrect = $completedAttempts->sum('correct_answers');
        $totalQuestions = $completedAttempts->sum('total_questions');
        $averageScore = $totalAttempts > 0 ? $completedAttempts->avg('score') : 0;

        return [
            'total_attempts' => $totalAttempts,
            'total_questions' => $totalQuestions,
            'total_correct' => $totalCorrect,
            'average_score' => round($averageScore, 2),
            'completion_rate' => $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100, 2) : 0,
        ];
    }

    /**
     * Lấy chi tiết một bài luyện tập với các câu trả lời.
     */
    public function getPracticeDetails(PracticeAttempt $attempt): array
    {
        $answers = $attempt->answers()->with('question')->get();

        return [
            'attempt' => $attempt,
            'answers' => $answers,
            'summary' => [
                'total_questions' => $attempt->total_questions,
                'correct_answers' => $attempt->correct_answers,
                'score' => $attempt->score,
                'duration' => $attempt->duration_in_minutes,
                'subject' => $attempt->subject,
                'topic' => $attempt->topic,
                'difficulty' => $attempt->difficulty,
            ],
        ];
    }
}
