<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Tests\TestCase;

class AcademicCalendarPhaseNineTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_edit_details_without_silently_changing_session_time(): void
    {
        [$teacher, $schedule] = $this->ownedSession('LIFE-A');

        $this->actingAs($teacher)->put(route('teacher.calendar.sessions.update', $schedule), [
            'title' => 'Nội dung buổi học đã cập nhật',
            'delivery_mode' => 'online',
            'meeting_url' => 'https://meet.example.test/session',
            'location' => null,
            'description' => 'Tài liệu và nội dung mới.',
            'session_date' => '2030-01-01',
            'start_time' => '20:00',
        ])->assertRedirect();

        $schedule->refresh();
        $this->assertSame('Nội dung buổi học đã cập nhật', $schedule->title);
        $this->assertSame('2026-08-12', $schedule->session_date->toDateString());
        $this->assertSame('08:00', substr($schedule->start_time, 0, 5));
        $this->assertSame($teacher->id, $schedule->updated_by);
    }

    public function test_reschedule_preserves_original_and_creates_linked_replacement(): void
    {
        [$teacher, $schedule] = $this->ownedSession('LIFE-B');

        $this->actingAs($teacher)->post(route('teacher.calendar.sessions.reschedule', $schedule), [
            'type' => 'regular',
            'title' => $schedule->title,
            'session_date' => '2026-08-14',
            'start_time' => '13:30',
            'end_time' => '15:00',
            'delivery_mode' => 'offline',
            'reschedule_reason' => 'Giáo viên có lịch công tác đột xuất tại trường.',
        ])->assertRedirect(route('teacher.calendar.index', ['date' => '2026-08-14']));

        $schedule->refresh();
        $replacement = ClassroomSchedule::query()->where('rescheduled_from_id', $schedule->id)->firstOrFail();

        $this->assertSame(ClassroomSchedule::STATUS_RESCHEDULED, $schedule->status);
        $this->assertSame('Giáo viên có lịch công tác đột xuất tại trường.', $schedule->reschedule_reason);
        $this->assertSame('2026-08-12', $schedule->session_date->toDateString());
        $this->assertSame('2026-08-14', $replacement->session_date->toDateString());
        $this->assertSame(ClassroomSchedule::STATUS_SCHEDULED, $replacement->status);
    }

    public function test_only_owner_can_reschedule_and_only_scheduled_session_can_complete(): void
    {
        [$owner, $schedule] = $this->ownedSession('LIFE-C');
        $other = $this->createUser(['role' => 'teacher']);

        $this->actingAs($other)->post(route('teacher.calendar.sessions.reschedule', $schedule), [
            'type' => 'regular', 'title' => $schedule->title, 'session_date' => '2026-08-14',
            'start_time' => '13:30', 'end_time' => '15:00', 'delivery_mode' => 'offline',
            'reschedule_reason' => 'Không có quyền thay đổi lịch của giáo viên khác.',
        ])->assertForbidden();

        $this->actingAs($owner)->post(route('teacher.calendar.sessions.complete', $schedule))
            ->assertRedirect();
        $this->assertSame(ClassroomSchedule::STATUS_COMPLETED, $schedule->fresh()->status);

        $this->actingAs($owner)->post(route('teacher.calendar.sessions.complete', $schedule))
            ->assertForbidden();
    }

    private function ownedSession(string $code): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id, 'teacher_id' => $teacher->id,
            'type' => Classroom::TYPE_STANDALONE, 'name' => 'Lifecycle '.$code,
            'code' => $code, 'slug' => strtolower($code), 'status' => 'active',
        ]);
        $schedule = ClassroomSchedule::query()->create([
            'classroom_id' => $classroom->id, 'type' => ClassroomSchedule::TYPE_REGULAR,
            'delivery_mode' => ClassroomSchedule::DELIVERY_OFFLINE,
            'status' => ClassroomSchedule::STATUS_SCHEDULED, 'title' => 'Buổi học vòng đời',
            'session_date' => '2026-08-12', 'start_time' => '08:00', 'end_time' => '10:00',
            'created_by' => $teacher->id, 'updated_by' => $teacher->id,
        ]);

        return [$teacher, $schedule];
    }
}
