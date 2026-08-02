<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\ExamManagement\Models\ExamQuestion;
use Mindigo\Notification\Notifications\ExamAssigned;
use Mindigo\TeacherExam\Services\TeacherExamService;
use Tests\TestCase;

class ExamMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishing_an_exam_notifies_each_assigned_student_only_once(): void
    {
        Notification::fake();
        [$teacher, $students, $classroom, $exam] = $this->fixture(2);
        $service = app(TeacherExamService::class);

        $service->publish($exam);
        $service->notifyAssignedStudents($exam->fresh());

        Notification::assertSentTo(
            $students,
            ExamAssigned::class,
            fn (ExamAssigned $notification) => $notification->examId === $exam->id
        );
        Notification::assertCount(2);
        $this->assertNotNull($exam->fresh()->assignment_notified_at);
    }

    public function test_teacher_monitoring_reports_classroom_and_live_attempt_progress(): void
    {
        [$teacher, $students, $classroom, $exam] = $this->fixture(2);
        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'user_id' => $students->first()->id,
            'status' => 'in_progress',
            'submitted_at' => null,
            'last_activity_at' => now(),
            'expires_at' => now()->addMinutes(20),
        ]);

        $data = app(TeacherExamService::class)->monitoringData($exam, $teacher);
        $rows = collect($data['students']->items());

        $this->assertSame(2, $data['summary']['assigned']);
        $this->assertSame(1, $data['summary']['started']);
        $this->assertSame(1, $data['summary']['online']);
        $this->assertSame(2, $data['classroomStats']->first()['assigned']);
        $this->assertSame('in_progress', $rows->firstWhere('id', $attempt->user_id)['status']);
        $this->assertTrue($rows->firstWhere('id', $attempt->user_id)['online']);

        $this->actingAs($teacher)
            ->get(route('teacher.exams.monitor', $exam))
            ->assertOk()
            ->assertSee(__('teacher-exam::app.classroom_dashboard'));

        $this->actingAs($teacher)
            ->getJson(route('teacher.exams.monitor.data', ['exam' => $exam, 'status' => 'in_progress']))
            ->assertOk()
            ->assertJsonPath('summary.in_progress', 1)
            ->assertJsonCount(1, 'students');
    }

    public function test_heartbeat_updates_activity_and_rejects_another_student(): void
    {
        [, $students, , $exam] = $this->fixture(2);
        $attempt = ExamAttempt::factory()->create([
            'exam_id' => $exam->id,
            'user_id' => $students->first()->id,
            'status' => 'in_progress',
            'submitted_at' => null,
            'last_activity_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($students->first())
            ->postJson(route('student.exams.heartbeat', $attempt))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertTrue($attempt->fresh()->last_activity_at->isAfter(now()->subMinute()));

        $this->actingAs($students->last())
            ->postJson(route('student.exams.heartbeat', $attempt))
            ->assertForbidden();
    }

    public function test_another_teacher_cannot_open_the_monitor(): void
    {
        [, , , $exam] = $this->fixture();

        /** @var User $otherTeacher */
        $otherTeacher = $this->createUser(['role' => 'teacher']);

        $this->actingAs($otherTeacher)
            ->get(route('teacher.exams.monitor', $exam))
            ->assertForbidden();
    }

    public function test_monitor_filters_are_validated(): void
    {
        [$teacher, , , $exam] = $this->fixture();

        $this->actingAs($teacher)
            ->get(route('teacher.exams.monitor', ['exam' => $exam, 'sort' => 'invalid']))
            ->assertSessionHasErrors('sort');
    }

    /**
     * @return array{0: User, 1: Collection<int, User>, 2: Classroom, 3: Exam}
     */
    private function fixture(int $studentCount = 1): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $students = User::factory()->count($studentCount)->create(['role' => 'student']);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id,
            'teacher_id' => $teacher->id,
            'name' => 'Class 12A1',
            'code' => '12A1',
            'slug' => 'class-12a1-'.str()->random(6),
            'status' => 'active',
        ]);
        $classroom->students()->attach($students->pluck('id'), ['status' => 'active', 'joined_at' => now()]);
        $exam = Exam::factory()->draft()->create([
            'created_by' => $teacher->id,
            'audience' => ['roles' => ['student'], 'classrooms' => [$classroom->id]],
            'total_questions' => 1,
            'total_points' => 1,
        ]);
        ExamQuestion::query()->create([
            'exam_id' => $exam->id,
            'sort_order' => 1,
            'type' => 'single_choice',
            'difficulty' => 'easy',
            'content' => 'Monitoring test question',
            'options' => [['id' => 'a', 'content' => 'Answer A'], ['id' => 'b', 'content' => 'Answer B']],
            'correct_answers' => ['a'],
            'points' => 1,
        ]);

        return [$teacher, $students, $classroom, $exam];
    }
}
