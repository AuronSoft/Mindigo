<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Tests\TestCase;

class AcademicCalendarPhaseElevenTest extends TestCase
{
    use RefreshDatabase;

    public function test_substitute_sees_pending_assignment_and_can_accept_it_without_owner_permissions(): void
    {
        [$owner, $substitute, $schedule] = $this->assignment();

        $this->actingAs($substitute)->get(route('teacher.calendar.index', ['date' => '2026-08-12']))
            ->assertOk()
            ->assertSee('Lịch dạy thay Phase 11')
            ->assertViewHas('events', function ($events) use ($schedule): bool {
                $event = $events->firstWhere('sourceId', $schedule->id);

                return $event
                    && $event->metadata['substitute_response_url'] === route('teacher.calendar.sessions.substitute.respond', $schedule)
                    && $event->metadata['can_manage_session'] === false
                    && ! isset($event->metadata['attendance_code']);
            });

        $this->actingAs($substitute)->post(route('teacher.calendar.sessions.substitute.respond', $schedule), [
            'decision' => 'accept',
        ])->assertRedirect();

        $this->assertSame(ClassroomSchedule::SUBSTITUTE_ACCEPTED, $schedule->fresh()->substitute_status);
        $this->assertNotNull($schedule->fresh()->substitute_responded_at);
        $this->actingAs($owner)->post(route('teacher.calendar.sessions.substitute.respond', $schedule), ['decision' => 'accept'])->assertForbidden();
    }

    public function test_declining_requires_a_clear_reason_and_removes_the_session_from_workload(): void
    {
        [, $substitute, $schedule] = $this->assignment();

        $this->actingAs($substitute)->post(route('teacher.calendar.sessions.substitute.respond', $schedule), [
            'decision' => 'decline', 'response_note' => 'Bận',
        ])->assertSessionHasErrors('response_note');

        $this->actingAs($substitute)->post(route('teacher.calendar.sessions.substitute.respond', $schedule), [
            'decision' => 'decline', 'response_note' => 'Đã có lịch giảng khác được xác nhận trong cùng khung giờ.',
        ])->assertRedirect();

        $schedule->refresh();
        $this->assertSame(ClassroomSchedule::SUBSTITUTE_DECLINED, $schedule->substitute_status);
        $this->assertSame('Đã có lịch giảng khác được xác nhận trong cùng khung giờ.', $schedule->substitute_response_note);

        $this->actingAs($substitute)->get(route('teacher.calendar.index', ['date' => '2026-08-12']))
            ->assertOk()->assertViewHas('summary', fn (array $summary) => $summary['class_sessions'] === 0 && (float) $summary['hours'] === 0.0);
    }

    private function assignment(): array
    {
        $owner = $this->createUser(['role' => 'teacher']);
        $substitute = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create([
            'created_by' => $owner->id, 'teacher_id' => $owner->id, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Lớp Phase 11', 'code' => 'PHASE11', 'slug' => 'phase-11', 'status' => 'active',
        ]);
        $schedule = ClassroomSchedule::query()->create([
            'classroom_id' => $classroom->id, 'type' => ClassroomSchedule::TYPE_REGULAR,
            'delivery_mode' => ClassroomSchedule::DELIVERY_OFFLINE, 'status' => ClassroomSchedule::STATUS_SCHEDULED,
            'title' => 'Lịch dạy thay Phase 11', 'session_date' => '2026-08-12', 'start_time' => '08:00', 'end_time' => '10:00',
            'substitute_teacher_id' => $substitute->id, 'substitute_status' => ClassroomSchedule::SUBSTITUTE_PENDING,
            'created_by' => $owner->id, 'updated_by' => $owner->id,
        ]);

        return [$owner, $substitute, $schedule];
    }
}
