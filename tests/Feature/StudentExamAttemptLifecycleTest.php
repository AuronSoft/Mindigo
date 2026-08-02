<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamQuestion;
use Mindigo\StudentExam\Services\ExamService;
use Tests\TestCase;

class StudentExamAttemptLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_starting_the_same_exam_resumes_the_active_attempt(): void
    {
        [$student, $exam] = $this->examFixture();
        $service = app(ExamService::class);

        $firstAttempt = $service->startAttempt($exam, $student->id);
        $resumedAttempt = $service->startAttempt($exam, $student->id);

        $this->assertTrue($firstAttempt->is($resumedAttempt));
        $this->assertSame('in_progress', $resumedAttempt->status);
        $this->assertSame($exam->questions()->pluck('id')->all(), $resumedAttempt->question_order);
    }

    public function test_autosaved_answers_are_restored_and_used_when_time_expires(): void
    {
        [$student, $exam, $question] = $this->examFixture();
        $service = app(ExamService::class);
        $attempt = $service->startAttempt($exam, $student->id);

        $service->saveAnswer($attempt, $question->id, 'a');
        $this->assertSame(['a'], $service->getSavedAnswers($attempt)->get($question->id)->answer);

        $service->autoSubmit($attempt);
        $attempt->refresh();

        $this->assertSame('expired', $attempt->status);
        $this->assertSame('1.00', $attempt->score);
        $this->assertSame('100.00', $attempt->percentage);
    }

    private function examFixture(): array
    {
        $student = $this->createUser(['role' => 'student']);
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id,
            'teacher_id' => $teacher->id,
            'name' => 'Lifecycle class',
            'code' => 'LIFE',
            'slug' => 'lifecycle-class',
            'status' => 'active',
        ]);
        $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);
        $exam = Exam::factory()->create([
            'created_by' => $teacher->id,
            'audience' => ['roles' => ['student'], 'classrooms' => [$classroom->id]],
            'duration_minutes' => 45,
            'total_questions' => 1,
            'total_points' => 1,
            'passing_score' => 1,
            'shuffle_questions' => false,
        ]);
        $question = ExamQuestion::query()->create([
            'exam_id' => $exam->id,
            'sort_order' => 1,
            'type' => 'single_choice',
            'difficulty' => 'easy',
            'content' => '2 + 2 bằng bao nhiêu?',
            'options' => [['id' => 'a', 'content' => '4'], ['id' => 'b', 'content' => '5']],
            'correct_answers' => ['a'],
            'points' => 1,
        ]);

        return [$student, $exam, $question];
    }
}
