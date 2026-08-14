<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\AcademicCalendar\Data\CalendarQuery;
use Mindigo\AcademicCalendar\Services\AcademicCalendarService;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Tests\TestCase;

class LiveSessionCalendarIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_linked_live_room_is_one_calendar_event_with_provider_and_secure_deep_link(): void
    {
        [$teacher, $student, $classroom] = $this->context();
        $schedule = $this->schedule($teacher->id, $classroom->id);
        $session = $this->liveSession($teacher->id, $classroom->id, $schedule->id);
        $from = CarbonImmutable::instance($schedule->session_date)->startOfDay();

        $events = app(AcademicCalendarService::class)->events(new CalendarQuery(
            viewer: $student,
            from: $from,
            to: $from->addDay(),
            timezone: config('app.timezone'),
        ));

        $this->assertCount(1, $events);
        $event = $events->first();
        $this->assertSame('classroom_schedule:'.$schedule->id, $event->id);
        $this->assertSame('google_meet', $event->metadata['provider']);
        $this->assertSame($session->id, $event->metadata['live_session_id']);
        $this->assertSame(route('student.live-sessions.room', $session), $event->url);
    }

    public function test_student_external_deep_link_redirects_only_after_mindigo_access_flow(): void
    {
        [$teacher, $student, $classroom] = $this->context();
        $schedule = $this->schedule($teacher->id, $classroom->id);
        $session = $this->liveSession($teacher->id, $classroom->id, $schedule->id);

        $this->actingAs($student)->get(route('student.live-sessions.room', $session))
            ->assertRedirect('https://meet.google.com/abc-defg-hij');
        $this->assertDatabaseHas('live_session_participants', [
            'live_session_id' => $session->id,
            'user_id' => $student->id,
            'admission_status' => 'admitted',
        ]);
    }

    private function context(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE, 'name' => 'Phase 15', 'code' => 'P15-'.str()->random(6), 'slug' => 'p15-'.str()->random(8), 'status' => 'active']);
        $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);

        return [$teacher, $student, $classroom];
    }

    private function schedule(int $teacherId, int $classroomId): ClassroomSchedule
    {
        return ClassroomSchedule::query()->create([
            'classroom_id' => $classroomId, 'type' => 'regular', 'delivery_mode' => 'online', 'status' => 'scheduled',
            'title' => 'Linked online lesson', 'session_date' => today(), 'start_time' => '09:00:00',
            'end_time' => '11:00:00', 'created_by' => $teacherId, 'updated_by' => $teacherId,
        ]);
    }

    private function liveSession(int $teacherId, int $classroomId, int $scheduleId): LiveSession
    {
        return LiveSession::query()->create([
            'classroom_id' => $classroomId, 'classroom_schedule_id' => $scheduleId, 'teacher_id' => $teacherId, 'created_by' => $teacherId,
            'title' => 'Linked online lesson', 'room_name' => 'phase-15-room', 'provider' => 'google_meet', 'provider_meeting_id' => 'event-15',
            'provider_join_url' => 'https://meet.google.com/abc-defg-hij', 'provider_host_url' => 'https://meet.google.com/abc-defg-hij',
            'provider_status' => 'confirmed', 'fallback_provider' => 'native', 'sync_status' => 'synced', 'session_type' => 'regular',
            'room_settings' => ['waiting_room_enabled' => false], 'scheduled_start' => now()->subHour(), 'scheduled_end' => now()->addHour(),
            'started_at' => now()->subHour(), 'status' => 'live',
        ]);
    }
}
