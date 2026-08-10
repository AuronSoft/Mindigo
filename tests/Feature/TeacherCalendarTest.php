<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Tests\TestCase;

class TeacherCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_calendar_only_renders_owned_classroom_events(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $otherTeacher = $this->createUser(['role' => 'teacher']);
        $mine = $this->classroom($teacher->id, 'TCAL-A');
        $other = $this->classroom($otherTeacher->id, 'TCAL-B');
        $this->createSession($mine, 'My calendar session');
        $this->createSession($other, 'Hidden calendar session');

        $response = $this->actingAs($teacher)->get(route('teacher.calendar.index', ['date' => '2026-08-12']));

        $response->assertOk()
            ->assertSee('My calendar session')
            ->assertDontSee('Hidden calendar session')
            ->assertSee(__('teacher-calendar::app.title'));
    }

    public function test_teacher_can_filter_the_calendar_by_owned_classroom(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $first = $this->classroom($teacher->id, 'TCAL-C');
        $second = $this->classroom($teacher->id, 'TCAL-D');
        $this->createSession($first, 'First classroom session');
        $this->createSession($second, 'Second classroom session');

        $this->actingAs($teacher)->get(route('teacher.calendar.index', [
            'date' => '2026-08-12', 'classroom_id' => $first->id,
        ]))->assertOk()->assertSee('First classroom session')->assertDontSee('Second classroom session');
    }

    public function test_teacher_can_quick_create_a_standalone_session_from_calendar(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroom($teacher->id, 'TCAL-E');

        $this->actingAs($teacher)->post(route('teacher.calendar.sessions.store', $classroom), [
            'type' => 'regular', 'title' => 'Quick calendar session', 'session_date' => '2026-08-13',
            'start_time' => '14:00', 'end_time' => '15:30', 'delivery_mode' => 'offline',
        ])->assertRedirect(route('teacher.calendar.index', ['date' => '2026-08-13']));

        $this->assertDatabaseHas('classroom_schedules', [
            'classroom_id' => $classroom->id, 'title' => 'Quick calendar session', 'created_by' => $teacher->id,
        ]);
    }

    public function test_teacher_cannot_quick_create_for_another_teachers_classroom(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $otherTeacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroom($otherTeacher->id, 'TCAL-F');

        $this->actingAs($teacher)->post(route('teacher.calendar.sessions.store', $classroom), [
            'type' => 'regular', 'title' => 'Forbidden session', 'session_date' => '2026-08-13',
            'start_time' => '14:00', 'end_time' => '15:30',
        ])->assertForbidden();
    }

    public function test_teacher_can_cancel_an_owned_session_with_a_reason(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroom($teacher->id, 'TCAL-G');
        $session = $this->createSession($classroom, 'Session to cancel');

        $this->actingAs($teacher)->post(route('teacher.calendar.sessions.cancel', $session), [
            'cancel_reason' => 'Giáo viên cần nghỉ vì lý do sức khỏe đột xuất.',
        ])->assertRedirect();

        $this->assertDatabaseHas('classroom_schedules', [
            'id' => $session->id, 'status' => 'cancelled', 'updated_by' => $teacher->id,
        ]);
    }

    private function classroom(int $teacherId, string $code): Classroom
    {
        return Classroom::query()->create([
            'created_by' => $teacherId, 'teacher_id' => $teacherId, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Calendar '.$code, 'code' => $code, 'slug' => strtolower($code), 'status' => 'active',
        ]);
    }

    private function createSession(Classroom $classroom, string $title): ClassroomSchedule
    {
        return ClassroomSchedule::query()->create([
            'classroom_id' => $classroom->id, 'type' => 'regular', 'title' => $title,
            'session_date' => '2026-08-12', 'start_time' => '08:00', 'end_time' => '10:00',
        ]);
    }
}
