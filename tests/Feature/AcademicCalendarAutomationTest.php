<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mindigo\AcademicCalendar\Notifications\CalendarEventChanged;
use Mindigo\AcademicCalendar\Notifications\CalendarEventReminder;
use Mindigo\AcademicCalendar\Observers\ClassroomScheduleObserver;
use Mindigo\Profile\Models\NotificationPreference;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Tests\TestCase;

class AcademicCalendarAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_creation_notifies_only_active_members_who_allow_updates(): void
    {
        Notification::fake();
        $teacher = $this->createUser(['role' => 'teacher']);
        $allowed = $this->createUser(['role' => 'student']);
        $muted = $this->createUser(['role' => 'student']);
        $inactive = $this->createUser(['role' => 'student']);
        $classroom = $this->classroom($teacher->id, 'AUTO-A');
        $classroom->students()->attach($allowed->id, ['status' => 'active']);
        $classroom->students()->attach($muted->id, ['status' => 'active']);
        $classroom->students()->attach($inactive->id, ['status' => 'inactive']);
        NotificationPreference::query()->create([
            'user_id' => $muted->id, 'notif_calendar_updates' => false, 'notif_calendar_reminders' => true,
        ]);
        $schedule = $this->createSession($classroom);

        app(ClassroomScheduleObserver::class)->created($schedule);

        Notification::assertSentTo($allowed, CalendarEventChanged::class);
        Notification::assertNotSentTo($muted, CalendarEventChanged::class);
        Notification::assertNotSentTo($inactive, CalendarEventChanged::class);
    }

    public function test_reminder_command_is_idempotent_for_the_same_event_and_window(): void
    {
        Notification::fake();
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = $this->classroom($teacher->id, 'AUTO-B');
        $classroom->students()->attach($student->id, ['status' => 'active']);
        $this->createSession($classroom);

        $this->artisan('calendar:send-reminders', ['--at' => '2026-08-11 08:00:00'])->assertSuccessful();
        $this->artisan('calendar:send-reminders', ['--at' => '2026-08-11 08:02:00'])->assertSuccessful();

        Notification::assertSentToTimes($student, CalendarEventReminder::class, 1);
        $this->assertDatabaseCount('academic_calendar_reminders', 2);
        $this->assertDatabaseHas('academic_calendar_reminders', [
            'user_id' => $student->id, 'reminder_key' => '24h',
        ]);
    }

    public function test_cancelled_events_and_muted_users_do_not_receive_reminders(): void
    {
        Notification::fake();
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $muted = $this->createUser(['role' => 'student']);
        $classroom = $this->classroom($teacher->id, 'AUTO-C');
        $classroom->students()->attach($student->id, ['status' => 'active']);
        $classroom->students()->attach($muted->id, ['status' => 'active']);
        NotificationPreference::query()->create([
            'user_id' => $muted->id, 'notif_calendar_updates' => true, 'notif_calendar_reminders' => false,
        ]);
        $this->createSession($classroom, ['status' => 'cancelled']);

        $this->artisan('calendar:send-reminders', ['--at' => '2026-08-11 08:00:00'])->assertSuccessful();

        Notification::assertNotSentTo($student, CalendarEventReminder::class);
        Notification::assertNotSentTo($muted, CalendarEventReminder::class);
        $this->assertDatabaseCount('academic_calendar_reminders', 0);
    }

    public function test_user_can_save_calendar_notification_preferences(): void
    {
        $student = $this->createUser(['role' => 'student']);

        $this->actingAs($student)->put(route('profile.notifications'), [
            'notif_new_quiz' => 1, 'notif_system_news' => 1,
            'notif_calendar_updates' => 0, 'notif_calendar_reminders' => 0,
        ])->assertRedirect();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $student->id, 'notif_calendar_updates' => false, 'notif_calendar_reminders' => false,
        ]);
    }

    private function classroom(int $teacherId, string $code): Classroom
    {
        return Classroom::query()->create([
            'created_by' => $teacherId, 'teacher_id' => $teacherId, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Automation '.$code, 'code' => $code, 'slug' => strtolower($code), 'status' => 'active',
        ]);
    }

    private function createSession(Classroom $classroom, array $attributes = []): ClassroomSchedule
    {
        return ClassroomSchedule::withoutEvents(fn () => ClassroomSchedule::query()->create(array_merge([
            'classroom_id' => $classroom->id, 'type' => 'regular', 'delivery_mode' => 'offline',
            'status' => 'scheduled', 'title' => 'Automated calendar session', 'session_date' => '2026-08-12',
            'start_time' => '08:00', 'end_time' => '10:00',
        ], $attributes)));
    }
}
