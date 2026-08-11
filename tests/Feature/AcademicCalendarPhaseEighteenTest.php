<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomAttendance;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherClassroom\Services\AttendanceReportService;
use Mindigo\TeacherClassroom\Services\TeacherClassroomService;
use Tests\TestCase;

class AcademicCalendarPhaseEighteenTest extends TestCase
{
    use RefreshDatabase;

    public function test_late_and_excused_attendance_require_control_details(): void
    {
        [$teacher, $student, $classroom] = $this->context();

        $this->actingAs($teacher)->post(route('teacher.classrooms.attendance.save', $classroom), [
            'attendance_date' => '2026-09-07',
            'records' => [$student->id => ['status' => 'late']],
        ])->assertSessionHasErrors("records.{$student->id}.late_minutes");
        $this->actingAs($teacher)->post(route('teacher.classrooms.attendance.save', $classroom), [
            'attendance_date' => '2026-09-07',
            'records' => [$student->id => ['status' => 'excused', 'absence_reason' => '']],
        ])->assertSessionHasErrors("records.{$student->id}.absence_reason");
    }

    public function test_manual_corrections_store_editor_and_immutable_revision_history(): void
    {
        [$teacher, $student, $classroom] = $this->context();
        $service = app(TeacherClassroomService::class);
        $service->saveAttendance($classroom, '2026-09-07', [
            $student->id => ['status' => 'late', 'late_minutes' => 12, 'remarks' => 'Traffic delay'],
        ], $teacher, 'Recorded after checking the classroom arrival log.');
        $service->saveAttendance($classroom, '2026-09-07', [
            $student->id => ['status' => 'excused', 'absence_reason' => 'Approved medical appointment'],
        ], $teacher, 'Parent supplied a valid medical document.');

        $attendance = ClassroomAttendance::query()->firstOrFail();
        $this->assertSame('excused', $attendance->status);
        $this->assertSame($teacher->id, $attendance->updated_by);
        $this->assertNull($attendance->late_minutes);
        $this->assertSame(2, $attendance->revisions()->count());
        $latest = $attendance->revisions()->first();
        $this->assertSame('late', $latest->old_values['status']);
        $this->assertSame('excused', $latest->new_values['status']);
        $this->assertSame($teacher->id, $latest->changed_by);
    }

    public function test_code_check_in_uses_session_late_threshold(): void
    {
        Carbon::setTestNow('2026-09-07 08:20:00');
        [$teacher, $student, $classroom] = $this->context();
        $schedule = ClassroomSchedule::query()->create([
            'classroom_id' => $classroom->id, 'type' => ClassroomSchedule::TYPE_REGULAR,
            'delivery_mode' => ClassroomSchedule::DELIVERY_OFFLINE, 'status' => ClassroomSchedule::STATUS_SCHEDULED,
            'title' => 'Threshold session', 'session_date' => '2026-09-07', 'start_time' => '08:00', 'end_time' => '10:00',
        ]);
        $service = app(TeacherClassroomService::class);
        $session = $service->openScheduleAttendance($schedule, $teacher, 30, 10);
        $attendance = $service->checkInWithCode($classroom, $student, $session->code);

        $this->assertSame('late', $attendance->status);
        $this->assertSame(20, $attendance->late_minutes);
        $this->assertSame(10, $session->late_after_minutes);
        $this->assertSame(1, $attendance->revisions()->count());
    }

    public function test_class_and_course_summary_and_csv_export_are_owner_scoped(): void
    {
        [$teacher, $student, $classroom] = $this->context();
        $other = $this->createUser(['role' => 'teacher']);
        app(TeacherClassroomService::class)->saveAttendance($classroom, '2026-09-07', [
            $student->id => ['status' => 'present'],
        ], $teacher);
        app(TeacherClassroomService::class)->saveAttendance($classroom, '2026-09-09', [
            $student->id => ['status' => 'late', 'late_minutes' => 8],
        ], $teacher);

        $summary = app(AttendanceReportService::class)->summary($classroom);
        $this->assertSame(2, $summary['total']);
        $this->assertSame(1, $summary['present']);
        $this->assertSame(1, $summary['late']);
        $this->assertSame(2, $summary['sessions']);

        $this->actingAs($other)->get(route('teacher.classrooms.attendance.export', $classroom))->assertForbidden();
        $this->actingAs($teacher)->get(route('teacher.classrooms.attendance.export', $classroom))
            ->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    private function context(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Phase 18 classroom', 'code' => 'PH18'.str()->upper(str()->random(4)),
            'slug' => 'phase-18-'.str()->lower(str()->random(5)), 'status' => 'active',
        ]);
        $classroom->students()->attach($student->id, ['status' => 'active']);

        return [$teacher, $student, $classroom];
    }
}
