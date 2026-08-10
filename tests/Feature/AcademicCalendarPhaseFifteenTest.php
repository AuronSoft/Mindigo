<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mindigo\AcademicCalendar\Notifications\CalendarEventChanged;
use Mindigo\AcademicCalendar\Observers\ClassroomScheduleObserver;
use Mindigo\Profile\Models\NotificationPreference;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Tests\TestCase;

class AcademicCalendarPhaseFifteenTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_changes_notify_students_primary_assistant_and_substitute_with_role_aware_links(): void
    {
        Notification::fake();
        $teacher = $this->createUser(['role' => 'teacher']);
        $assistant = $this->createUser(['role' => 'teacher']);
        $substitute = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = $this->classroom($teacher->id, $assistant->id);
        $classroom->students()->attach($student->id, ['status' => 'active']);
        $schedule = $this->schedule($classroom, ['substitute_teacher_id' => $substitute->id]);

        app(ClassroomScheduleObserver::class)->created($schedule);

        foreach ([$teacher, $assistant, $substitute, $student] as $recipient) {
            Notification::assertSentTo($recipient, CalendarEventChanged::class);
        }
        Notification::assertSentTo($student, CalendarEventChanged::class, function (CalendarEventChanged $notification) use ($student): bool {
            $data = $notification->toArray($student);

            return $data['audience'] === 'student' && str_contains($data['url'], '/student/schedule');
        });
        Notification::assertSentTo($assistant, CalendarEventChanged::class, function (CalendarEventChanged $notification) use ($assistant): bool {
            $data = $notification->toArray($assistant);

            return $data['audience'] === 'teacher' && str_contains($data['url'], '/teacher/calendar');
        });
    }

    public function test_preferences_inactive_members_and_duplicate_roles_are_respected(): void
    {
        Notification::fake();
        $teacher = $this->createUser(['role' => 'teacher']);
        $mutedAssistant = $this->createUser(['role' => 'teacher']);
        $activeStudent = $this->createUser(['role' => 'student']);
        $inactiveStudent = $this->createUser(['role' => 'student']);
        $classroom = $this->classroom($teacher->id, $mutedAssistant->id);
        $classroom->students()->attach($activeStudent->id, ['status' => 'active']);
        $classroom->students()->attach($inactiveStudent->id, ['status' => 'inactive']);
        NotificationPreference::query()->create([
            'user_id' => $mutedAssistant->id, 'notif_calendar_updates' => false, 'notif_calendar_reminders' => true,
        ]);

        app(ClassroomScheduleObserver::class)->created($this->schedule($classroom, ['substitute_teacher_id' => $teacher->id]));

        Notification::assertSentToTimes($teacher, CalendarEventChanged::class, 1);
        Notification::assertSentTo($activeStudent, CalendarEventChanged::class);
        Notification::assertNotSentTo($mutedAssistant, CalendarEventChanged::class);
        Notification::assertNotSentTo($inactiveStudent, CalendarEventChanged::class);
    }

    public function test_publishing_and_material_updates_notify_but_irrelevant_edits_do_not(): void
    {
        Notification::fake();
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = $this->classroom($teacher->id);
        $classroom->students()->attach($student->id, ['status' => 'active']);
        $schedule = $this->schedule($classroom, ['status' => ClassroomSchedule::STATUS_DRAFT]);
        $observer = app(ClassroomScheduleObserver::class);

        ClassroomSchedule::withoutEvents(fn () => $schedule->update(['description' => 'Internal note only']));
        $observer->updated($schedule);
        Notification::assertNothingSent();

        ClassroomSchedule::withoutEvents(fn () => $schedule->update(['status' => ClassroomSchedule::STATUS_SCHEDULED]));
        $observer->updated($schedule);
        Notification::assertSentToTimes($student, CalendarEventChanged::class, 1);

        ClassroomSchedule::withoutEvents(fn () => $schedule->update(['title' => 'Updated lesson title']));
        $observer->updated($schedule);
        Notification::assertSentToTimes($student, CalendarEventChanged::class, 2);
    }

    private function classroom(int $teacherId, ?int $assistantId = null): Classroom
    {
        return Classroom::query()->create([
            'created_by' => $teacherId, 'teacher_id' => $teacherId, 'assistant_id' => $assistantId,
            'type' => Classroom::TYPE_STANDALONE, 'name' => 'Phase 15 classroom',
            'code' => 'PH15'.str()->upper(str()->random(4)), 'slug' => 'phase-15-'.str()->lower(str()->random(5)), 'status' => 'active',
        ]);
    }

    private function schedule(Classroom $classroom, array $attributes = []): ClassroomSchedule
    {
        return ClassroomSchedule::withoutEvents(fn () => ClassroomSchedule::query()->create(array_merge([
            'classroom_id' => $classroom->id, 'type' => ClassroomSchedule::TYPE_REGULAR,
            'delivery_mode' => ClassroomSchedule::DELIVERY_OFFLINE, 'status' => ClassroomSchedule::STATUS_SCHEDULED,
            'title' => 'Phase 15 session', 'session_date' => '2026-09-14', 'start_time' => '08:00', 'end_time' => '10:00',
        ], $attributes)));
    }
}
