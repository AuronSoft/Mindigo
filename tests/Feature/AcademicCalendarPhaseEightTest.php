<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Tests\TestCase;

class AcademicCalendarPhaseEightTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_dashboard_calendar_uses_owned_academic_calendar_events(): void
    {
        CarbonImmutable::setTestNow('2026-08-10 07:00:00');
        $teacher = $this->createUser(['role' => 'teacher']);
        $otherTeacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroom($teacher->id, 'DASH-CAL-A');
        $otherClassroom = $this->classroom($otherTeacher->id, 'DASH-CAL-B');
        $this->createCalendarSession($classroom, 'Buổi học dashboard thật');
        $this->createCalendarSession($otherClassroom, 'Không được lộ trên dashboard');

        $this->actingAs($teacher)->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('data-dashboard-calendar', false)
            ->assertSee('Buổi học dashboard thật')
            ->assertDontSee('Không được lộ trên dashboard')
            ->assertSee(route('teacher.calendar.index', ['view' => 'day', 'date' => '2026-08-10']))
            ->assertSee(route('teacher.calendar.index', ['view' => 'week', 'date' => '2026-08-10']));
    }

    private function classroom(int $teacherId, string $code): Classroom
    {
        return Classroom::query()->create([
            'created_by' => $teacherId,
            'teacher_id' => $teacherId,
            'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Dashboard '.$code,
            'code' => $code,
            'slug' => strtolower($code),
            'status' => 'active',
        ]);
    }

    private function createCalendarSession(Classroom $classroom, string $title): ClassroomSchedule
    {
        return ClassroomSchedule::query()->create([
            'classroom_id' => $classroom->id,
            'type' => ClassroomSchedule::TYPE_REGULAR,
            'status' => ClassroomSchedule::STATUS_SCHEDULED,
            'title' => $title,
            'session_date' => '2026-08-10',
            'start_time' => '08:30',
            'end_time' => '10:00',
        ]);
    }
}
