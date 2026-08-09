<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;
use Mindigo\ExamManagement\Models\ExamQuestion;
use Mindigo\ExamManagement\Services\ExamService as CoreExamService;
use Mindigo\StudentExam\Services\ExamService as StudentExamService;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherExam\Services\TeacherExamService;
use Tests\TestCase;

class ExamHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_access_or_change_another_students_attempt(): void
    {
        [, $owner, $otherStudent, , $exam] = $this->fixture();
        $attempt = app(StudentExamService::class)->startAttempt($exam, $owner->id);

        $this->actingAs($otherStudent)->get(route('student.exams.take', $attempt))->assertForbidden();
        $this->actingAs($otherStudent)->postJson(route('student.exams.autosave', $attempt), [
            'question_id' => $exam->questions()->value('id'),
            'answer' => 'a',
        ])->assertForbidden();
        $this->actingAs($otherStudent)->get(route('student.exams.result', $attempt))->assertForbidden();
    }

    public function test_non_student_role_cannot_use_student_exam_endpoints(): void
    {
        [$teacher, , , , $exam] = $this->fixture();

        $this->actingAs($teacher)
            ->postJson(route('student.exams.start', $exam))
            ->assertForbidden();
    }

    public function test_student_cannot_start_an_exam_assigned_to_another_classroom(): void
    {
        [, , , , $exam] = $this->fixture();
        $outsider = $this->createUser(['role' => 'student']);

        $this->actingAs($outsider)
            ->post(route('student.exams.start', $exam))
            ->assertForbidden();
    }

    public function test_invalid_question_cannot_be_injected_into_autosave_or_submit(): void
    {
        [, $student, , , $exam] = $this->fixture();
        $otherQuestion = ExamQuestion::query()->create([
            'exam_id' => Exam::factory()->create()->id,
            'sort_order' => 1,
            'type' => 'single_choice',
            'difficulty' => 'easy',
            'content' => 'Foreign question',
            'options' => [],
            'correct_answers' => [],
            'points' => 1,
        ]);
        $attempt = app(StudentExamService::class)->startAttempt($exam, $student->id);

        $this->actingAs($student)->postJson(route('student.exams.autosave', $attempt), [
            'question_id' => $otherQuestion->id,
            'answer' => 'a',
        ])->assertNotFound();

        $this->actingAs($student)->post(route('student.exams.submit', $attempt), [
            'answers' => [$otherQuestion->id => 'a'],
        ])->assertSessionHasErrors("answers.{$otherQuestion->id}");
    }

    public function test_late_and_duplicate_submissions_cannot_change_the_result(): void
    {
        [, $student, , , $exam, $question] = $this->fixture();
        $service = app(StudentExamService::class);
        $attempt = $service->startAttempt($exam, $student->id);
        $attempt->forceFill(['expires_at' => now()->subSecond()])->save();

        $result = $service->submitAttempt($attempt, ['answers' => [$question->id => 'a']]);

        $this->assertSame('expired', $result->status);
        $this->assertSame('0.00', $result->score);

        $this->expectException(ValidationException::class);
        $service->submitAttempt($result, ['answers' => [$question->id => 'a']]);
    }

    public function test_autosave_cannot_modify_a_submitted_attempt(): void
    {
        [, $student, , , $exam, $question] = $this->fixture();
        $service = app(StudentExamService::class);
        $attempt = $service->startAttempt($exam, $student->id);
        $service->submitAttempt($attempt, ['answers' => [$question->id => 'a']]);

        $this->assertFalse($service->saveAnswer($attempt->fresh(), $question->id, 'b'));
        $this->assertSame(['a'], $attempt->answers()->first()->answer);
    }

    public function test_closing_an_exam_does_not_destroy_an_active_attempt(): void
    {
        [, $student, , , $exam, $question] = $this->fixture();
        $service = app(StudentExamService::class);
        $attempt = $service->startAttempt($exam, $student->id);
        $exam->forceFill(['status' => 'closed'])->save();

        $this->assertTrue($service->saveAnswer($attempt, $question->id, 'a'));
        $this->assertSame('submitted', $service->submitAttempt($attempt, [
            'answers' => [$question->id => 'a'],
        ])->status);
    }

    public function test_foreign_teacher_and_stale_grading_window_are_rejected(): void
    {
        [$teacher, $student, , , $exam] = $this->fixture('essay');
        $attempt = app(StudentExamService::class)->startAttempt($exam, $student->id);
        app(StudentExamService::class)->submitAttempt($attempt, ['answers' => [$exam->questions()->value('id') => 'Essay']]);
        $attempt->refresh();
        $answer = $attempt->answers()->firstOrFail();
        $otherTeacher = $this->createUser(['role' => 'teacher']);

        $this->actingAs($otherTeacher)
            ->put(route('teacher.exams.attempts.grade.update', [$exam, $attempt]), $this->gradePayload($answer, 0))
            ->assertForbidden();

        $payload = $this->gradePayload($answer, 0);
        $payload['grades'][$answer->id]['points'] = 2;
        $this->actingAs($teacher)
            ->put(route('teacher.exams.attempts.grade.update', [$exam, $attempt]), $payload)
            ->assertSessionHasErrors("grades.{$answer->id}.points");

        $service = app(TeacherExamService::class);
        $service->gradeAttempt($attempt, [$answer->id => ['points' => 1, 'feedback' => null]], $teacher, 0);

        $this->expectException(ValidationException::class);
        $service->gradeAttempt($attempt, [$answer->id => ['points' => 0, 'feedback' => null]], $teacher, 0);
    }

    public function test_teacher_cannot_open_another_teachers_exam_management_url(): void
    {
        [, , , , $exam] = $this->fixture();
        $otherTeacher = $this->createUser(['role' => 'teacher']);

        $this->actingAs($otherTeacher)
            ->get(route('exams.edit', $exam))
            ->assertForbidden();
    }

    public function test_exam_with_attempts_cannot_be_deleted(): void
    {
        [, $student, , , $exam] = $this->fixture();
        app(StudentExamService::class)->startAttempt($exam, $student->id);

        $this->expectException(ValidationException::class);
        app(CoreExamService::class)->delete($exam);
    }

    private function gradePayload(ExamAttemptAnswer $answer, int $version): array
    {
        return [
            'grading_version' => $version,
            'grades' => [$answer->id => ['points' => 1, 'feedback' => null]],
        ];
    }

    private function fixture(string $questionType = 'single_choice'): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $otherStudent = $this->createUser(['role' => 'student']);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id,
            'teacher_id' => $teacher->id,
            'name' => 'Hardening class',
            'code' => 'HARDEN',
            'slug' => 'hardening-class-'.str()->random(6),
            'status' => 'active',
        ]);
        $classroom->students()->attach([$student->id, $otherStudent->id], ['status' => 'active', 'joined_at' => now()]);
        $exam = Exam::factory()->create([
            'created_by' => $teacher->id,
            'audience' => ['roles' => ['student'], 'classrooms' => [$classroom->id]],
            'duration_minutes' => 30,
            'max_attempts' => 1,
            'total_questions' => 1,
            'total_points' => 1,
            'passing_score' => 1,
        ]);
        $question = ExamQuestion::query()->create([
            'exam_id' => $exam->id,
            'sort_order' => 1,
            'type' => $questionType,
            'difficulty' => 'easy',
            'content' => 'Hardening question',
            'options' => $questionType === 'essay' ? [] : [['id' => 'a', 'content' => 'A'], ['id' => 'b', 'content' => 'B']],
            'correct_answers' => $questionType === 'essay' ? [] : ['a'],
            'points' => 1,
        ]);

        return [$teacher, $student, $otherStudent, $classroom, $exam, $question];
    }
}
