<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\AcademicCalendar\Models\AcademicCalendarException;
use Mindigo\AuditLog\Models\AuditLog;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Tests\TestCase;

class AcademicCalendarPhaseSeventeenTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_lifecycle_records_actor_context_and_before_after_values(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroom($teacher->id);
        $this->actingAs($teacher);

        $schedule = ClassroomSchedule::query()->create([
            'classroom_id' => $classroom->id, 'type' => ClassroomSchedule::TYPE_REGULAR,
            'delivery_mode' => ClassroomSchedule::DELIVERY_OFFLINE, 'status' => ClassroomSchedule::STATUS_SCHEDULED,
            'title' => 'Audited session', 'session_date' => '2026-09-07', 'start_time' => '08:00', 'end_time' => '10:00',
            'created_by' => $teacher->id, 'updated_by' => $teacher->id,
        ]);

        $created = $this->audit('session_created');
        $this->assertSame($teacher->id, $created->user_id);
        $this->assertSame($classroom->id, $created->metadata['classroom_id']);
        $this->assertSame('2026-09-07', substr($created->new_values['session_date'], 0, 10));

        $schedule->update(['session_date' => '2026-09-09', 'start_time' => '09:00', 'end_time' => '11:00']);
        $rescheduled = $this->audit('session_rescheduled');
        $this->assertSame('2026-09-07', substr($rescheduled->old_values['session_date'], 0, 10));
        $this->assertSame('2026-09-09', substr($rescheduled->new_values['session_date'], 0, 10));

        $schedule->update(['status' => ClassroomSchedule::STATUS_CANCELLED, 'cancel_reason' => 'Classroom unavailable due to maintenance.']);
        $cancelled = $this->audit('session_cancelled');
        $this->assertSame(ClassroomSchedule::STATUS_SCHEDULED, $cancelled->old_values['status']);
        $this->assertSame(ClassroomSchedule::STATUS_CANCELLED, $cancelled->new_values['status']);
        $this->assertArrayHasKey('cancel_reason', $cancelled->new_values);
    }

    public function test_assistant_and_substitute_workflows_have_distinct_audit_actions(): void
    {
        $owner = $this->createUser(['role' => 'teacher']);
        $firstAssistant = $this->createUser(['role' => 'teacher']);
        $secondAssistant = $this->createUser(['role' => 'teacher']);
        $substitute = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroom($owner->id);
        $this->actingAs($owner);

        $classroom->update(['assistant_id' => $firstAssistant->id]);
        $this->assertSame($firstAssistant->id, $this->audit('assistant_assigned')->new_values['assistant_id']);
        $classroom->update(['assistant_id' => $secondAssistant->id]);
        $this->assertSame($firstAssistant->id, $this->audit('assistant_replaced')->old_values['assistant_id']);
        $classroom->update(['assistant_id' => null]);
        $this->assertNull($this->audit('assistant_removed')->new_values['assistant_id']);

        $schedule = ClassroomSchedule::query()->create([
            'classroom_id' => $classroom->id, 'type' => ClassroomSchedule::TYPE_REGULAR,
            'delivery_mode' => ClassroomSchedule::DELIVERY_OFFLINE, 'status' => ClassroomSchedule::STATUS_SCHEDULED,
            'title' => 'Substitute session', 'session_date' => '2026-09-14', 'start_time' => '08:00', 'end_time' => '10:00',
            'created_by' => $owner->id, 'updated_by' => $owner->id,
        ]);
        $schedule->update(['substitute_teacher_id' => $substitute->id, 'substitute_status' => ClassroomSchedule::SUBSTITUTE_PENDING]);
        $this->assertSame($substitute->id, $this->audit('substitute_assigned')->new_values['substitute_teacher_id']);

        $this->actingAs($substitute);
        $schedule->update(['substitute_status' => ClassroomSchedule::SUBSTITUTE_ACCEPTED, 'substitute_responded_at' => now()]);
        $accepted = $this->audit('substitute_accepted');
        $this->assertSame($substitute->id, $accepted->user_id);
        $this->assertSame(ClassroomSchedule::SUBSTITUTE_ACCEPTED, $accepted->new_values['substitute_status']);
    }

    public function test_calendar_exception_create_update_and_delete_are_audited_by_scope(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $this->actingAs($admin);
        $exception = AcademicCalendarException::query()->create([
            'created_by' => $admin->id, 'exception_date' => '2026-09-02',
            'kind' => AcademicCalendarException::KIND_NO_CLASS, 'title' => 'System closure',
            'reason' => 'The platform follows the approved academic calendar.',
        ]);

        $created = $this->audit('calendar_exception_created');
        $this->assertSame('global', $created->metadata['exception_scope']);
        $exception->update(['title' => 'Updated system closure']);
        $this->assertSame('System closure', $this->audit('calendar_exception_updated')->old_values['title']);
        $exception->delete();
        $deleted = $this->audit('calendar_exception_deleted');
        $this->assertSame($exception->id, $deleted->auditable_id);
        $this->assertSame('Updated system closure', $deleted->old_values['title']);
    }

    public function test_unrelated_classroom_updates_do_not_pollute_calendar_audit_log(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroom($teacher->id);
        $this->actingAs($teacher);
        $before = AuditLog::query()->where('module', 'academic-calendar')->count();

        $classroom->update(['name' => 'Renamed classroom']);

        $this->assertSame($before, AuditLog::query()->where('module', 'academic-calendar')->count());
    }

    private function audit(string $action): AuditLog
    {
        return AuditLog::query()->where('module', 'academic-calendar')->where('action', $action)->latest('id')->firstOrFail();
    }

    private function classroom(int $teacherId): Classroom
    {
        return Classroom::query()->create([
            'created_by' => $teacherId, 'teacher_id' => $teacherId, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Audit classroom', 'code' => 'AUD'.str()->upper(str()->random(5)),
            'slug' => 'audit-'.str()->lower(str()->random(6)), 'status' => 'active',
        ]);
    }
}
