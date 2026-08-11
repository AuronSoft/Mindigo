<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mindigo\AcademicCalendar\Data\CalendarQuery;
use Mindigo\AcademicCalendar\Enums\CalendarEventKind;
use Mindigo\AcademicCalendar\Models\AcademicCalendarException;
use Mindigo\AcademicCalendar\Services\AcademicCalendarService;
use Mindigo\AuditLog\Models\AuditLog;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomAttendance;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherCourse\Models\Course;
use Tests\TestCase;

class AcademicCalendarPhaseNineteenTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_identity_constraints_reject_duplicate_attendance_exception_and_session_start(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = $this->classroom($teacher->id);
        $attendance = [
            'classroom_id' => $classroom->id, 'student_id' => $student->id, 'session_date' => '2026-09-07',
            'status' => 'present', 'method' => 'manual', 'updated_by' => $teacher->id,
        ];
        ClassroomAttendance::query()->create($attendance);
        $this->expectConstraint(fn () => ClassroomAttendance::query()->create($attendance));

        $exception = ['exception_date' => '2026-09-09', 'kind' => AcademicCalendarException::KIND_NO_CLASS, 'title' => 'Closure'];
        AcademicCalendarException::query()->create($exception);
        $this->expectConstraint(fn () => AcademicCalendarException::query()->create($exception));

        $schedule = [
            'classroom_id' => $classroom->id, 'type' => ClassroomSchedule::TYPE_REGULAR,
            'delivery_mode' => ClassroomSchedule::DELIVERY_OFFLINE, 'status' => ClassroomSchedule::STATUS_SCHEDULED,
            'title' => 'First', 'session_date' => '2026-09-14', 'start_time' => '08:00', 'end_time' => '10:00',
        ];
        $originalSchedule = ClassroomSchedule::query()->create($schedule);
        $this->expectConstraint(fn () => ClassroomSchedule::query()->create([...$schedule, 'title' => 'Duplicate']));
        $originalSchedule->update(['status' => ClassroomSchedule::STATUS_CANCELLED]);
        $this->assertNotNull(ClassroomSchedule::query()->create([...$schedule, 'title' => 'Replacement'])->id);
    }

    public function test_permissions_are_enforced_across_classroom_calendar_attendance_and_substitute_routes(): void
    {
        $owner = $this->createUser(['role' => 'teacher']);
        $other = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = $this->classroom($owner->id);
        $schedule = ClassroomSchedule::query()->create([
            'classroom_id' => $classroom->id, 'type' => ClassroomSchedule::TYPE_REGULAR,
            'delivery_mode' => ClassroomSchedule::DELIVERY_OFFLINE, 'status' => ClassroomSchedule::STATUS_SCHEDULED,
            'title' => 'Protected session', 'session_date' => '2026-09-14', 'start_time' => '08:00', 'end_time' => '10:00',
            'substitute_teacher_id' => $other->id, 'substitute_status' => ClassroomSchedule::SUBSTITUTE_PENDING,
        ]);

        $this->actingAs($student)->get(route('teacher.classrooms.show', $classroom))->assertRedirect();
        $this->actingAs($other)->get(route('teacher.classrooms.attendance.export', $classroom))->assertForbidden();
        $this->actingAs($other)->post(route('teacher.calendar.sessions.cancel', $schedule), ['cancel_reason' => 'Unauthorized cancellation attempt.'])->assertForbidden();
        $this->actingAs($owner)->post(route('teacher.calendar.sessions.substitute.respond', $schedule), ['decision' => 'accept'])->assertForbidden();
        $this->actingAs($other)->post(route('teacher.calendar.sessions.substitute.respond', $schedule), ['decision' => 'accept'])->assertRedirect();
    }

    public function test_manual_course_session_cannot_exceed_course_end_date(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $course = Course::query()->create([
            'teacher_id' => $teacher->id, 'name' => 'Bounded course', 'slug' => 'bounded-course',
            'publication_status' => Course::PUBLICATION_PUBLISHED, 'is_active' => true,
            'starts_at' => '2026-09-01', 'ends_at' => '2026-09-30', 'schedule_days' => ['mon'], 'study_time' => '08:00 - 10:00',
        ]);
        $classroom = $this->classroom($teacher->id, $course->id);

        $this->actingAs($teacher)->post(route('teacher.classrooms.schedules.store', $classroom), [
            'type' => ClassroomSchedule::TYPE_MAKEUP, 'delivery_mode' => ClassroomSchedule::DELIVERY_OFFLINE,
            'status' => ClassroomSchedule::STATUS_SCHEDULED, 'title' => 'Outside course',
            'session_date' => '2026-10-05', 'start_time' => '08:00', 'end_time' => '10:00',
            'makeup_reason' => 'A proposed replacement outside the approved course range.',
        ])->assertSessionHasErrors('session_date');
    }

    public function test_attendance_changes_are_in_shared_audit_log(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = $this->classroom($teacher->id);
        $this->actingAs($teacher);
        $attendance = ClassroomAttendance::query()->create([
            'classroom_id' => $classroom->id, 'student_id' => $student->id, 'session_date' => '2026-09-07',
            'status' => 'present', 'method' => 'manual', 'updated_by' => $teacher->id,
        ]);
        $attendance->update(['status' => 'late', 'late_minutes' => 9]);

        $this->assertDatabaseHas('audit_logs', ['module' => 'academic-calendar', 'action' => 'attendance_recorded', 'auditable_id' => $attendance->id]);
        $this->assertDatabaseHas('audit_logs', ['module' => 'academic-calendar', 'action' => 'attendance_corrected', 'auditable_id' => $attendance->id]);
        $this->assertSame($teacher->id, AuditLog::query()->where('action', 'attendance_corrected')->value('user_id'));
    }

    public function test_large_calendar_range_has_bounded_queries_and_render_data(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroom($teacher->id);
        $rows = [];
        foreach (range(0, 29) as $day) {
            foreach (range(7, 18) as $hour) {
                $date = CarbonImmutable::parse('2026-09-01')->addDays($day)->toDateString();
                $rows[] = [
                    'classroom_id' => $classroom->id, 'type' => ClassroomSchedule::TYPE_REGULAR,
                    'delivery_mode' => ClassroomSchedule::DELIVERY_OFFLINE, 'status' => ClassroomSchedule::STATUS_SCHEDULED,
                    'title' => "Load session {$day}-{$hour}", 'session_date' => $date,
                    'start_time' => sprintf('%02d:00:00', $hour), 'end_time' => sprintf('%02d:45:00', $hour),
                    'created_at' => now(), 'updated_at' => now(),
                ];
            }
        }
        ClassroomSchedule::query()->insert($rows);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $started = hrtime(true);
        $events = app(AcademicCalendarService::class)->events(new CalendarQuery(
            viewer: $teacher, from: CarbonImmutable::parse('2026-09-01'), to: CarbonImmutable::parse('2026-10-01'),
        ));
        $elapsedMs = (hrtime(true) - $started) / 1_000_000;

        $this->assertCount(360, $events->where('kind', CalendarEventKind::ClassSession));
        $this->assertLessThanOrEqual(18, count(DB::getQueryLog()));
        $this->assertLessThan(2500, $elapsedMs);
    }

    public function test_calendar_and_attendance_pages_expose_accessible_controls(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = $this->classroom($teacher->id);
        $classroom->students()->attach($student->id, ['status' => 'active']);

        $this->actingAs($teacher)->get(route('teacher.classrooms.show', [$classroom, 'tab' => 'attendance']))
            ->assertOk()->assertSee('aria-label=', false)->assertSee('role="tablist"', false);
        $this->actingAs($teacher)->get(route('teacher.calendar.index'))->assertOk()->assertSee('aria-label=', false);
        $this->actingAs($student)->get(route('student.schedule.index'))->assertOk()->assertSee('aria-label=', false);
    }

    private function expectConstraint(callable $callback): void
    {
        try {
            $callback();
            $this->fail('Expected the database uniqueness constraint to reject the duplicate.');
        } catch (QueryException) {
            $this->addToAssertionCount(1);
        }
    }

    private function classroom(int $teacherId, ?int $courseId = null): Classroom
    {
        return Classroom::query()->create([
            'created_by' => $teacherId, 'teacher_id' => $teacherId,
            'type' => $courseId ? Classroom::TYPE_COURSE : Classroom::TYPE_STANDALONE, 'course_id' => $courseId,
            'name' => 'Phase 19 classroom', 'code' => 'PH19'.str()->upper(str()->random(4)),
            'slug' => 'phase-19-'.str()->lower(str()->random(5)), 'status' => 'active',
        ]);
    }
}
