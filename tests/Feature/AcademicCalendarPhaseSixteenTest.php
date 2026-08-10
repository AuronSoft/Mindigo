<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\AcademicCalendar\Data\CalendarQuery;
use Mindigo\AcademicCalendar\Enums\CalendarEventKind;
use Mindigo\AcademicCalendar\Models\AcademicCalendarException;
use Mindigo\AcademicCalendar\Services\AcademicCalendarService;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherDashboard\Services\TeacherDashboardService;
use Tests\TestCase;

class AcademicCalendarPhaseSixteenTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sees_global_course_and_classroom_closures_without_cross_course_leakage(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course($teacher->id, 'visible');
        $otherCourse = $this->course($teacher->id, 'hidden');
        $classroom = $this->classroom($teacher->id, $course->id, 'VISIBLE');
        $classroom->students()->attach($student->id, ['status' => 'active']);

        $this->exception(null, null, '2026-09-02', 'Global closure');
        $this->exception($course->id, null, '2026-09-07', 'Course closure');
        $this->exception($course->id, $classroom->id, '2026-09-09', 'Class closure');
        $this->exception($otherCourse->id, null, '2026-09-14', 'Hidden closure');

        $events = $this->events($student);

        $this->assertSame(['Global closure', 'Course closure', 'Class closure'], $events->pluck('title')->all());
        $this->assertTrue($events->every(fn ($event) => $event->kind === CalendarEventKind::AcademicClosure));
        $this->assertTrue($events->every(fn ($event) => $event->metadata['all_day'] === true));
    }

    public function test_assistant_and_substitute_can_read_relevant_calendar_but_cannot_manage_sessions(): void
    {
        $owner = $this->createUser(['role' => 'teacher']);
        $assistant = $this->createUser(['role' => 'teacher']);
        $substitute = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroom($owner->id, null, 'TEACHERS', $assistant->id);
        $session = ClassroomSchedule::query()->create([
            'classroom_id' => $classroom->id, 'type' => ClassroomSchedule::TYPE_REGULAR,
            'delivery_mode' => ClassroomSchedule::DELIVERY_OFFLINE, 'status' => ClassroomSchedule::STATUS_SCHEDULED,
            'title' => 'Shared teaching session', 'session_date' => '2026-09-07', 'start_time' => '08:00', 'end_time' => '10:00',
            'substitute_teacher_id' => $substitute->id,
        ]);
        $this->exception(null, $classroom->id, '2026-09-09', 'Shared class closure');

        foreach ([$assistant, $substitute] as $viewer) {
            $events = app(AcademicCalendarService::class)->events(new CalendarQuery(
                viewer: $viewer, from: CarbonImmutable::parse('2026-09-01'), to: CarbonImmutable::parse('2026-09-30'),
            ));
            $sessionEvent = $events->firstWhere('id', 'classroom_schedule:'.$session->id);

            $this->assertNotNull($sessionEvent);
            $this->assertSame(['view'], $sessionEvent->actions);
            $this->assertTrue($events->contains('title', 'Shared class closure'));
        }
    }

    public function test_teacher_dashboard_snapshot_contains_closures_without_counting_them_as_classes(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroom($teacher->id, null, 'DASHBOARD');
        $this->exception(null, null, '2026-09-02', 'System closure');
        $this->exception(null, $classroom->id, '2026-09-10', 'Class closure');

        $snapshot = app(TeacherDashboardService::class)->getCalendarSnapshot($teacher, CarbonImmutable::parse('2026-09-10'));

        $this->assertTrue($snapshot['eventsByDay']['2026-09-10']->contains('title', 'Class closure'));
        $this->assertSame(0, $snapshot['todayClassCount']);
        $this->assertSame(2, $snapshot['monthEventCount']);
    }

    private function events($viewer)
    {
        return app(AcademicCalendarService::class)->events(new CalendarQuery(
            viewer: $viewer,
            from: CarbonImmutable::parse('2026-09-01'),
            to: CarbonImmutable::parse('2026-09-30'),
            kinds: [CalendarEventKind::AcademicClosure],
        ));
    }

    private function exception(?int $courseId, ?int $classroomId, string $date, string $title): void
    {
        AcademicCalendarException::query()->create([
            'course_id' => $courseId, 'classroom_id' => $classroomId, 'exception_date' => $date,
            'kind' => AcademicCalendarException::KIND_NO_CLASS, 'title' => $title, 'reason' => 'Approved academic calendar exception.',
        ]);
    }

    private function course(int $teacherId, string $suffix): Course
    {
        return Course::query()->create([
            'teacher_id' => $teacherId, 'name' => 'Course '.$suffix, 'slug' => 'phase-16-'.$suffix,
            'publication_status' => Course::PUBLICATION_PUBLISHED, 'is_active' => true,
        ]);
    }

    private function classroom(int $teacherId, ?int $courseId, string $code, ?int $assistantId = null): Classroom
    {
        return Classroom::query()->create([
            'created_by' => $teacherId, 'teacher_id' => $teacherId, 'assistant_id' => $assistantId,
            'type' => $courseId ? Classroom::TYPE_COURSE : Classroom::TYPE_STANDALONE, 'course_id' => $courseId,
            'name' => 'Classroom '.$code, 'code' => $code, 'slug' => strtolower($code), 'status' => 'active',
        ]);
    }
}
