<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Models\ExamTemplateVersion;
use Mindigo\TeacherClassroom\Models\Classroom;
use Tests\TestCase;

class ExamSessionPhaseFourTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_schedules_locked_template_for_owned_classroom_and_snapshots_candidates(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student', 'name' => 'Candidate One']);
        $inactive = $this->createUser(['role' => 'student']);
        [$template, $version] = $this->readyTemplate($teacher);
        $classroom = $this->classroom($teacher, 'Class A');
        $classroom->students()->attach($student->id, ['status' => 'active']);
        $classroom->students()->attach($inactive->id, ['status' => 'inactive']);

        $this->actingAs($teacher)->post(route('teacher.exam-sessions.store'), $this->payload($version, [$classroom]))
            ->assertRedirect(route('teacher.exam-sessions.index'));

        $session = ExamSession::query()->firstOrFail();
        $this->assertSame(ExamSession::STATUS_SCHEDULED, $session->status);
        $this->assertSame(1, $session->assignments()->count());
        $this->assertSame(1, $session->candidates()->count());
        $this->assertSame('Candidate One', $session->candidates()->first()->name);
        $this->assertNotNull($session->scheduled_at);
        $this->assertTrue($version->fresh()->isLocked());
        $this->assertSame('ready', $template->fresh()->status);
    }

    public function test_candidate_is_deduplicated_across_multiple_classrooms(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        [, $version] = $this->readyTemplate($teacher);
        $first = $this->classroom($teacher, 'Class A');
        $second = $this->classroom($teacher, 'Class B');
        $first->students()->attach($student->id, ['status' => 'active']);
        $second->students()->attach($student->id, ['status' => 'active']);

        $this->actingAs($teacher)->post(route('teacher.exam-sessions.store'), $this->payload($version, [$first, $second]))->assertRedirect();

        $candidate = ExamSession::query()->firstOrFail()->candidates()->firstOrFail();
        $this->assertDatabaseCount('exam_candidates', 1);
        $this->assertEqualsCanonicalizing([$first->id, $second->id], $candidate->metadata['classroom_ids']);
    }

    public function test_teacher_cannot_schedule_unlocked_or_foreign_resources(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $other = $this->createUser(['role' => 'teacher']);
        $template = ExamTemplate::query()->create(['owner_id' => $teacher->id, 'title' => 'Draft', 'slug' => 'draft']);
        $unlocked = ExamTemplateVersion::query()->create(['exam_template_id' => $template->id, 'created_by' => $teacher->id, 'version' => 1, 'title' => 'Draft']);
        $foreignClassroom = $this->classroom($other, 'Foreign');

        $this->actingAs($teacher)->post(route('teacher.exam-sessions.store'), $this->payload($unlocked, [$foreignClassroom]))
            ->assertSessionHasErrors('exam_template_version_id');

        [, $locked] = $this->readyTemplate($teacher, 'Ready second');
        $this->actingAs($teacher)->post(route('teacher.exam-sessions.store'), $this->payload($locked, [$foreignClassroom]))
            ->assertSessionHasErrors('classroom_ids');
        $this->assertDatabaseCount('exam_sessions', 0);
    }

    public function test_only_teacher_can_open_localized_session_workspace(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $this->actingAs($teacher)->get(route('teacher.exam-sessions.index'))->assertOk()
            ->assertSee(__('Mindigo-exam-management::app.session_builder.title'));
        $this->actingAs($teacher)->get(route('teacher.exam-sessions.create'))->assertOk()
            ->assertSee(__('Mindigo-exam-management::app.session_builder.schedule'));

        foreach (['admin', 'student'] as $role) {
            $this->actingAs($this->createUser(['role' => $role]))->get(route('teacher.exam-sessions.index'))->assertRedirect();
        }
    }

    public function test_attempt_duration_cannot_exceed_exam_window(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        [, $version] = $this->readyTemplate($teacher);
        $classroom = $this->classroom($teacher, 'Window validation');
        $payload = $this->payload($version, [$classroom]);
        $payload['duration_minutes'] = 121;

        $this->actingAs($teacher)->post(route('teacher.exam-sessions.store'), $payload)
            ->assertSessionHasErrors('duration_minutes');

        $this->assertDatabaseCount('exam_sessions', 0);
    }

    private function readyTemplate(User $teacher, string $title = 'Ready template'): array
    {
        $slug = str($title)->slug().'-'.str()->lower(str()->random(6));
        $template = ExamTemplate::query()->create(['owner_id' => $teacher->id, 'title' => $title, 'slug' => $slug, 'status' => 'ready', 'ready_at' => now()]);
        $version = ExamTemplateVersion::query()->create(['exam_template_id' => $template->id, 'created_by' => $teacher->id, 'version' => 1, 'title' => $title, 'total_questions' => 10, 'total_points' => 10, 'locked_at' => now()]);

        return [$template, $version];
    }

    private function classroom(User $teacher, string $name): Classroom
    {
        $code = str()->upper(str()->random(8));

        return Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE, 'name' => $name, 'code' => $code, 'slug' => str($code)->lower(), 'status' => 'active']);
    }

    private function payload(ExamTemplateVersion $version, array $classrooms): array
    {
        return ['exam_template_version_id' => $version->id, 'title' => 'Final exam', 'starts_at' => now()->addDay()->format('Y-m-d H:i:s'), 'ends_at' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'), 'duration_minutes' => 90, 'max_attempts' => 1, 'passing_score' => 5, 'result_policy' => 'after_release', 'shuffle_questions' => true, 'shuffle_answers' => true, 'security_policy' => ['fullscreen' => true, 'tab_switch_detection' => true], 'classroom_ids' => collect($classrooms)->pluck('id')->all()];
    }
}
