<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Tests\TestCase;

class AcademicCalendarPhaseTenTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_scheduled_session_has_an_independent_attendance_session_and_record(): void
    {
        Carbon::setTestNow('2026-08-12 08:30:00');
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Phase 10', 'code' => 'PHASE10', 'slug' => 'phase-10', 'status' => 'active',
        ]);
        $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);
        $first = $this->schedule($classroom, $teacher->id, 'Ca sáng', '08:00', '10:00');
        $second = $this->schedule($classroom, $teacher->id, 'Ca chiều', '08:15', '10:15');

        $this->actingAs($teacher)->post(route('teacher.calendar.sessions.attendance.open', $first), ['duration_minutes' => 30])->assertRedirect();
        $firstSession = $first->attendanceSession()->firstOrFail();
        $this->actingAs($teacher)->post(route('teacher.calendar.sessions.attendance.open', $second), ['duration_minutes' => 30])->assertRedirect();

        $this->assertDatabaseCount('classroom_attendance_sessions', 2);
        $this->actingAs($student)->post(route('student.classrooms.attendance.check-in', $classroom), ['attendance_code' => $firstSession->code])->assertRedirect();
        $this->assertDatabaseHas('classroom_attendances', ['classroom_schedule_id' => $first->id, 'student_id' => $student->id]);
        $this->assertDatabaseMissing('classroom_attendances', ['classroom_schedule_id' => $second->id, 'student_id' => $student->id]);
    }

    public function test_attendance_cannot_be_opened_outside_the_session_window(): void
    {
        Carbon::setTestNow('2026-08-12 05:00:00');
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE, 'name' => 'Window', 'code' => 'WINDOW', 'slug' => 'window', 'status' => 'active']);
        $schedule = $this->schedule($classroom, $teacher->id, 'Buổi sáng', '08:00', '10:00');

        $this->actingAs($teacher)->post(route('teacher.calendar.sessions.attendance.open', $schedule), ['duration_minutes' => 30])->assertSessionHasErrors('attendance');
        $this->assertDatabaseCount('classroom_attendance_sessions', 0);
    }

    private function schedule(Classroom $classroom, int $teacherId, string $title, string $start, string $end): ClassroomSchedule
    {
        return ClassroomSchedule::query()->create([
            'classroom_id' => $classroom->id, 'type' => ClassroomSchedule::TYPE_REGULAR, 'delivery_mode' => ClassroomSchedule::DELIVERY_OFFLINE,
            'status' => ClassroomSchedule::STATUS_SCHEDULED, 'title' => $title, 'session_date' => '2026-08-12',
            'start_time' => $start, 'end_time' => $end, 'created_by' => $teacherId, 'updated_by' => $teacherId,
        ]);
    }
}
