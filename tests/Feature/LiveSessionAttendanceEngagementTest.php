<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Services\LiveSessionAttendanceService;
use Tests\TestCase;

final class LiveSessionAttendanceEngagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_connections_track_real_duration_lateness_and_engagement(): void
    {
        [$session, $teacher, $students] = $this->room();
        $student = $students[0];
        $service = app(LiveSessionAttendanceService::class);

        $this->travelTo($session->scheduled_start->copy()->addMinutes(15));
        $service->heartbeat($session, $student);
        $service->incrementEngagement($session, $student, 'chat_messages_count');
        $this->travel(5)->minutes();
        $service->leave($session, $student);
        $this->travel(1)->minutes();
        $service->heartbeat($session, $student);
        $service->incrementEngagement($session, $student, 'poll_votes_count');
        $this->travel(5)->minutes();
        $service->leave($session, $student);

        $attendance = $session->attendances()->where('user_id', $student->id)->firstOrFail();
        $this->assertSame(2, $attendance->join_count);
        $this->assertSame(600, $attendance->total_seconds);
        $this->assertSame(15, $attendance->late_minutes);
        $this->assertSame('late', $attendance->attendance_status);
        $this->assertSame(1, $attendance->chat_messages_count);
        $this->assertSame(1, $attendance->poll_votes_count);
        $this->assertCount(2, $attendance->segments);
    }

    public function test_finalization_marks_missing_students_absent_and_teacher_exports_report(): void
    {
        [$session, $teacher, $students] = $this->room();
        $service = app(LiveSessionAttendanceService::class);
        $this->travelTo($session->scheduled_start->copy()->addMinute());
        $service->heartbeat($session, $students[0]);
        $this->travel(40)->minutes();
        $session->update(['status' => 'ended', 'ended_at' => now()]);
        $service->finalize($session->fresh());

        $this->assertDatabaseHas('live_session_attendances', ['live_session_id' => $session->id, 'user_id' => $students[1]->id, 'attendance_status' => 'absent']);
        $this->actingAs($teacher)->get(route('teacher.live-sessions.attendance.show', $session))->assertOk()->assertSee($students[0]->name)->assertSee($students[1]->name);
        $this->actingAs($teacher)->get(route('teacher.live-sessions.attendance.export', $session))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $outsider = $this->createUser(['role' => 'teacher']);
        $this->actingAs($outsider)->get(route('teacher.live-sessions.attendance.show', $session))->assertForbidden();
    }

    private function room(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $students = collect([$this->createUser(['role' => 'student']), $this->createUser(['role' => 'student'])]);
        $classroom = Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE, 'name' => 'Attendance class', 'code' => 'ATT-'.uniqid(), 'slug' => 'att-'.uniqid(), 'status' => 'active']);
        $students->each(fn ($student) => $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]));
        $start = now()->addHour()->startOfMinute();
        $session = LiveSession::query()->create(['classroom_id' => $classroom->id, 'teacher_id' => $teacher->id, 'created_by' => $teacher->id, 'title' => 'Attendance lesson', 'room_name' => 'attendance-'.uniqid(), 'provider' => 'native', 'provider_status' => 'live', 'fallback_provider' => 'native', 'sync_status' => 'not_required', 'session_type' => 'flexible', 'scheduled_start' => $start, 'scheduled_end' => $start->copy()->addHour(), 'started_at' => $start, 'status' => 'live']);

        return [$session, $teacher, $students];
    }
}
