<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Mindigo\AcademicCalendar\Data\CalendarEvent;
use Mindigo\AcademicCalendar\Data\CalendarQuery;
use Mindigo\AcademicCalendar\Enums\CalendarEventKind;
use Mindigo\AcademicCalendar\Services\AcademicCalendarService;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\TeacherAssignment\Models\Assignment;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Tests\TestCase;

class AcademicCalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_only_receives_events_from_active_classrooms_and_assigned_exams(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $otherStudent = $this->createUser(['role' => 'student']);
        $classroom = $this->classroom($teacher->id, 'CAL-A');
        $otherClassroom = $this->classroom($teacher->id, 'CAL-B');
        $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);
        $otherClassroom->students()->attach($otherStudent->id, ['status' => 'active', 'joined_at' => now()]);

        ClassroomSchedule::query()->create([
            'classroom_id' => $classroom->id, 'type' => 'regular', 'title' => 'Student session',
            'session_date' => '2026-08-12', 'start_time' => '08:00', 'end_time' => '10:00',
        ]);
        ClassroomSchedule::query()->create([
            'classroom_id' => $otherClassroom->id, 'type' => 'regular', 'title' => 'Hidden session',
            'session_date' => '2026-08-12', 'start_time' => '08:00', 'end_time' => '10:00',
        ]);
        Exam::factory()->create([
            'created_by' => $teacher->id, 'title' => 'Assigned exam', 'starts_at' => '2026-08-12 02:00:00',
            'ends_at' => '2026-08-12 03:00:00', 'audience' => ['roles' => ['student'], 'classrooms' => [$classroom->id]],
        ]);
        Exam::factory()->create([
            'created_by' => $teacher->id, 'title' => 'Unassigned exam', 'starts_at' => '2026-08-12 02:00:00',
            'ends_at' => '2026-08-12 03:00:00', 'audience' => ['roles' => ['student'], 'classrooms' => [$otherClassroom->id]],
        ]);

        $events = $this->service()->events($this->query($student));

        $this->assertEqualsCanonicalizing(['Student session', 'Assigned exam'], $events->pluck('title')->all());
        $this->assertNotContains('Hidden session', $events->pluck('title'));
        $this->assertNotContains('Unassigned exam', $events->pluck('title'));
    }

    public function test_calendar_aggregates_and_orders_all_supported_sources(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroom($teacher->id, 'CAL-C');

        Assignment::query()->create([
            'classroom_id' => $classroom->id, 'teacher_id' => $teacher->id, 'title' => 'Assignment',
            'due_date' => '2026-08-12 05:00:00', 'status' => 'published', 'submission_type' => 'both',
        ]);
        LiveSession::query()->create([
            'classroom_id' => $classroom->id, 'teacher_id' => $teacher->id, 'title' => 'Live class',
            'room_name' => 'calendar-room', 'provider' => 'jitsi', 'scheduled_start' => '2026-08-12 03:00:00',
            'scheduled_end' => '2026-08-12 04:00:00', 'status' => 'scheduled',
        ]);
        ClassroomSchedule::query()->create([
            'classroom_id' => $classroom->id, 'type' => 'regular', 'title' => 'Class session',
            'session_date' => '2026-08-12', 'start_time' => '08:00', 'end_time' => '09:00',
        ]);

        $events = $this->service()->events($this->query($teacher));

        $this->assertSame(['Live class', 'Assignment', 'Class session'], $events->pluck('title')->all());
        $this->assertContainsOnlyInstancesOf(CalendarEvent::class, $events);
    }

    public function test_kind_and_classroom_filters_are_applied_at_the_query_boundary(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroom($teacher->id, 'CAL-D');
        $otherClassroom = $this->classroom($teacher->id, 'CAL-E');
        foreach ([$classroom, $otherClassroom] as $index => $item) {
            ClassroomSchedule::query()->create([
                'classroom_id' => $item->id, 'type' => 'regular', 'title' => 'Session '.$index,
                'session_date' => '2026-08-12', 'start_time' => '08:00', 'end_time' => '09:00',
            ]);
        }

        $events = $this->service()->events(new CalendarQuery(
            viewer: $teacher,
            from: CarbonImmutable::parse('2026-08-11T17:00:00Z'),
            to: CarbonImmutable::parse('2026-08-13T17:00:00Z'),
            kinds: [CalendarEventKind::ClassSession],
            classroomIds: [$classroom->id],
        ));

        $this->assertCount(1, $events);
        $this->assertSame($classroom->id, $events->first()->classroomId);
    }

    public function test_calendar_query_rejects_invalid_ranges(): void
    {
        $student = $this->createUser(['role' => 'student']);

        $this->expectException(InvalidArgumentException::class);
        new CalendarQuery(
            viewer: $student,
            from: CarbonImmutable::parse('2026-08-12'),
            to: CarbonImmutable::parse('2026-08-11'),
        );
    }

    private function service(): AcademicCalendarService
    {
        return app(AcademicCalendarService::class);
    }

    private function query($viewer): CalendarQuery
    {
        return new CalendarQuery(
            viewer: $viewer,
            from: CarbonImmutable::parse('2026-08-11T17:00:00Z'),
            to: CarbonImmutable::parse('2026-08-13T17:00:00Z'),
        );
    }

    private function classroom(int $teacherId, string $code): Classroom
    {
        return Classroom::query()->create([
            'created_by' => $teacherId,
            'teacher_id' => $teacherId,
            'name' => 'Calendar '.$code,
            'code' => $code,
            'slug' => strtolower($code),
            'status' => 'active',
        ]);
    }
}
