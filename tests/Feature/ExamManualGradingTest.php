<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamQuestion;
use Mindigo\StudentExam\Services\ExamService as StudentExamService;
use Mindigo\TeacherExam\Services\TeacherExamService;
use Tests\TestCase;

class ExamManualGradingTest extends TestCase
{
    use RefreshDatabase;

    public function test_objective_answers_are_graded_and_essay_waits_for_teacher(): void
    {
        [$teacher, $student, $exam, $objective, $essay] = $this->mixedExamFixture();
        $studentService = app(StudentExamService::class);
        $attempt = $studentService->startAttempt($exam, $student->id);

        $studentService->submitAttempt($attempt, [
            'answers' => [$objective->id => 'a', $essay->id => 'Bài làm tự luận'],
        ]);
        $attempt->refresh()->load('answers');

        $this->assertNull($attempt->passed);
        $this->assertSame('1.00', $attempt->score);
        $this->assertFalse($attempt->answers->firstWhere('exam_question_id', $objective->id)->needs_review);
        $this->assertTrue($attempt->answers->firstWhere('exam_question_id', $essay->id)->needs_review);
        $this->assertTrue($studentService->getResult($attempt)['pending_review']);

        $essayAnswer = $attempt->answers->firstWhere('exam_question_id', $essay->id);
        app(TeacherExamService::class)->gradeAttempt($attempt, [
            $essayAnswer->id => ['points' => 1.5, 'feedback' => 'Lập luận tốt.'],
        ], $teacher);

        $attempt->refresh();
        $result = $studentService->getResult($attempt);

        $this->assertSame('2.50', $attempt->score);
        $this->assertTrue($attempt->passed);
        $this->assertNotNull($attempt->graded_at);
        $this->assertFalse($result['pending_review']);
        $this->assertTrue($result['show_review']);
    }

    private function mixedExamFixture(): array
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $exam = Exam::factory()->create([
            'created_by' => $teacher->id,
            'total_questions' => 2,
            'total_points' => 3,
            'passing_score' => 2,
            'show_results' => true,
        ]);
        $objective = ExamQuestion::query()->create([
            'exam_id' => $exam->id,
            'sort_order' => 1,
            'type' => 'single_choice',
            'difficulty' => 'easy',
            'content' => 'Chọn đáp án đúng',
            'options' => [['id' => 'a', 'content' => 'Đúng'], ['id' => 'b', 'content' => 'Sai']],
            'correct_answers' => ['a'],
            'points' => 1,
        ]);
        $essay = ExamQuestion::query()->create([
            'exam_id' => $exam->id,
            'sort_order' => 2,
            'type' => 'essay',
            'difficulty' => 'medium',
            'content' => 'Trình bày lời giải',
            'points' => 2,
        ]);

        return [$teacher, $student, $exam, $objective, $essay];
    }
}
