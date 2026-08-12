<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionAttendance;
use Mindigo\TeacherLiveSession\Services\LiveSessionReportService;
use Tests\TestCase;

class LiveSessionReportingTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_aggregates_attendance_engagement_reconnect_and_connection_errors(): void
    {
        [$teacher, $student, $classroom] = $this->context();
        $session = $this->liveSession($teacher->id, $classroom->id);
        $attendance = LiveSessionAttendance::query()->create([
            'live_session_id' => $session->id, 'user_id' => $student->id, 'attendance_status' => 'late',
            'joined_at' => now()->subMinutes(45), 'left_at' => now()->subMinutes(5), 'total_seconds' => 2400,
            'join_count' => 3, 'late_minutes' => 12, 'chat_messages_count' => 4, 'reactions_count' => 3,
            'hands_raised_count' => 2, 'poll_votes_count' => 1,
        ]);
        $attendance->segments()->create(['joined_at' => now()->subMinutes(45), 'last_seen_at' => now()->subMinutes(25), 'left_at' => now()->subMinutes(25), 'duration_seconds' => 1200, 'leave_reason' => 'connection_lost']);

        $report = app(LiveSessionReportService::class)->report($teacher, ['scope' => 'provider']);
        $row = $report['rows']->first();

        $this->assertSame('native', $row['label']);
        $this->assertSame(100.0, $row['attendance_rate']);
        $this->assertSame(40, $row['total_minutes']);
        $this->assertSame(1, $row['late']);
        $this->assertSame(1, $row['early_leave']);
        $this->assertSame(2, $row['reconnects']);
        $this->assertSame(1, $row['connection_errors']);
        $this->assertSame(4, $row['chat']);
    }

    public function test_teacher_report_is_tenant_scoped_and_all_planned_scopes_render(): void
    {
        [$teacher, $student, $classroom] = $this->context();
        $session = $this->liveSession($teacher->id, $classroom->id);
        LiveSessionAttendance::query()->create(['live_session_id' => $session->id, 'user_id' => $student->id, 'attendance_status' => 'present', 'total_seconds' => 600, 'join_count' => 1]);
        $other = $this->createUser(['role' => 'teacher']);
        $otherClassroom = Classroom::query()->create(['created_by' => $other->id, 'teacher_id' => $other->id, 'type' => Classroom::TYPE_STANDALONE, 'name' => 'Private class', 'code' => 'PRIVATE-'.str()->random(4), 'slug' => 'private-'.str()->random(6), 'status' => 'active']);
        $this->liveSession($other->id, $otherClassroom->id, 'Private session');

        foreach (['session', 'classroom', 'course', 'teacher', 'student', 'provider'] as $scope) {
            $this->actingAs($teacher)->get(route('teacher.live-sessions.reports.index', ['scope' => $scope]))
                ->assertOk()->assertDontSee('Private session');
        }
    }

    public function test_report_exports_csv_excel_and_pdf(): void
    {
        [$teacher, $student, $classroom] = $this->context();
        $session = $this->liveSession($teacher->id, $classroom->id);
        LiveSessionAttendance::query()->create(['live_session_id' => $session->id, 'user_id' => $student->id, 'attendance_status' => 'present', 'total_seconds' => 600, 'join_count' => 1]);

        foreach (['csv', 'xlsx', 'pdf'] as $format) {
            $this->actingAs($teacher)->get(route('teacher.live-sessions.reports.export', ['scope' => 'session', 'format' => $format]))
                ->assertOk()->assertDownload();
        }
    }

    private function context(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE, 'name' => 'Reporting class', 'code' => 'REPORT-'.str()->random(4), 'slug' => 'report-'.str()->random(6), 'status' => 'active']);

        return [$teacher, $student, $classroom];
    }

    private function liveSession(int $teacherId, int $classroomId, string $title = 'Reporting session'): LiveSession
    {
        return LiveSession::query()->create([
            'classroom_id' => $classroomId, 'teacher_id' => $teacherId, 'created_by' => $teacherId, 'title' => $title,
            'room_name' => 'report-'.str()->random(8), 'provider' => 'native', 'provider_status' => 'ended',
            'fallback_provider' => 'native', 'sync_status' => 'not_required', 'session_type' => 'flexible', 'room_settings' => [],
            'scheduled_start' => now()->subHour(), 'scheduled_end' => now(), 'started_at' => now()->subHour(), 'ended_at' => now(), 'status' => 'ended',
        ]);
    }
}
