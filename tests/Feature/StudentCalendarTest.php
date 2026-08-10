<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Tests\TestCase;

class StudentCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_only_sees_events_from_active_classroom_memberships(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $teacher = $this->createUser(['role' => 'teacher']);
        $active = $this->classroom($teacher->id, 'SCAL-A');
        $inactive = $this->classroom($teacher->id, 'SCAL-B');
        $active->students()->attach($student->id, ['status' => 'active']);
        $inactive->students()->attach($student->id, ['status' => 'inactive']);
        $this->createSession($active, 'Visible student session');
        $this->createSession($inactive, 'Hidden student session');

        $this->actingAs($student)->get(route('student.schedule.index', ['date' => '2026-08-12', 'view' => 'week']))
            ->assertOk()->assertSee('Visible student session')->assertDontSee('Hidden student session');
    }

    public function test_student_calendar_supports_every_planned_view(): void
    {
        $student = $this->createUser(['role' => 'student']);

        foreach (['today', 'week', 'month', 'schedule'] as $view) {
            $this->actingAs($student)->get(route('student.schedule.index', ['date' => '2026-08-12', 'view' => $view]))
                ->assertOk()->assertSee(__('student-schedule::app.view_'.$view));
        }
    }

    public function test_cancelled_session_remains_visible_with_reason_and_without_action(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroom($teacher->id, 'SCAL-C');
        $classroom->students()->attach($student->id, ['status' => 'active']);
        $this->createSession($classroom, 'Cancelled student session', [
            'status' => 'cancelled', 'cancel_reason' => 'Giáo viên có lịch công tác đột xuất.',
        ]);

        $this->actingAs($student)->get(route('student.schedule.index', ['date' => '2026-08-12', 'view' => 'schedule']))
            ->assertOk()->assertSee('Cancelled student session')->assertSee('Giáo viên có lịch công tác đột xuất.');
    }

    public function test_teacher_is_rejected_by_student_calendar_middleware(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);

        $this->actingAs($teacher)->get(route('student.schedule.index'))->assertRedirect();
    }

    private function classroom(int $teacherId, string $code): Classroom
    {
        return Classroom::query()->create([
            'created_by' => $teacherId, 'teacher_id' => $teacherId, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Student Calendar '.$code, 'code' => $code, 'slug' => strtolower($code), 'status' => 'active',
        ]);
    }

    private function createSession(Classroom $classroom, string $title, array $attributes = []): ClassroomSchedule
    {
        return ClassroomSchedule::query()->create(array_merge([
            'classroom_id' => $classroom->id, 'type' => 'regular', 'delivery_mode' => 'offline',
            'status' => 'scheduled', 'title' => $title, 'session_date' => '2026-08-12',
            'start_time' => '08:00', 'end_time' => '10:00',
        ], $attributes));
    }
}
