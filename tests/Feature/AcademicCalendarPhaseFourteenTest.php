<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\AcademicCalendar\Models\AcademicCalendarException;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherCourse\Models\Course;
use Tests\TestCase;

class AcademicCalendarPhaseFourteenTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_manage_global_and_course_closures(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $teacher = $this->createUser(['role' => 'teacher']);
        $course = $this->course($teacher->id);

        $this->actingAs($teacher)->get(route('admin.calendar-exceptions.index'))->assertForbidden();
        $this->actingAs($teacher)->post(route('admin.calendar-exceptions.store'), [
            'scope' => 'global', 'exception_date' => '2026-09-02', 'title' => 'Unauthorized', 'reason' => 'Teachers cannot create a platform closure.',
        ])->assertForbidden();

        $this->actingAs($admin)->post(route('admin.calendar-exceptions.store'), [
            'scope' => 'global', 'exception_date' => '2026-09-02', 'title' => 'National holiday', 'reason' => 'The entire platform follows the academic holiday.',
        ])->assertRedirect(route('admin.calendar-exceptions.index'));
        $this->actingAs($admin)->post(route('admin.calendar-exceptions.store'), [
            'scope' => 'course', 'course_id' => $course->id, 'exception_date' => '2026-09-09', 'title' => 'Course workshop', 'reason' => 'This course pauses for its required teacher workshop.',
        ])->assertRedirect(route('admin.calendar-exceptions.index'));

        $this->assertDatabaseHas('academic_calendar_exceptions', ['course_id' => null, 'classroom_id' => null, 'exception_date' => '2026-09-02 00:00:00']);
        $this->assertDatabaseHas('academic_calendar_exceptions', ['course_id' => $course->id, 'classroom_id' => null, 'exception_date' => '2026-09-09 00:00:00']);
        $this->actingAs($admin)->get(route('admin.calendar-exceptions.index', ['scope' => 'course', 'course_id' => $course->id]))
            ->assertOk()->assertSee('Course workshop')->assertDontSee('National holiday');
    }

    public function test_global_and_course_closures_are_both_enforced_by_course_planning(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $teacher = $this->createUser(['role' => 'teacher']);
        $course = $this->course($teacher->id);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_COURSE,
            'course_id' => $course->id, 'name' => 'Phase 14 classroom', 'code' => 'PHASE14', 'slug' => 'phase-14-classroom', 'status' => 'active',
        ]);

        foreach ([['global', null, '2026-09-02'], ['course', $course->id, '2026-09-07']] as [$scope, $courseId, $date]) {
            $this->actingAs($admin)->post(route('admin.calendar-exceptions.store'), [
                'scope' => $scope, 'course_id' => $courseId, 'exception_date' => $date,
                'title' => 'Academic closure', 'reason' => 'This date is unavailable under the approved calendar.',
            ])->assertSessionHasNoErrors();
        }

        $this->actingAs($teacher)->post(route('teacher.classrooms.schedules.generate-course-plan', $classroom), [
            'start_date' => '2026-09-01', 'session_count' => 2,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('classroom_schedules', ['session_date' => '2026-09-02 00:00:00']);
        $this->assertDatabaseMissing('classroom_schedules', ['session_date' => '2026-09-07 00:00:00']);
        $this->assertDatabaseHas('classroom_schedules', ['session_date' => '2026-09-09 00:00:00']);
        $this->assertDatabaseHas('classroom_schedules', ['session_date' => '2026-09-14 00:00:00']);
    }

    public function test_admin_cannot_delete_a_teacher_owned_classroom_exception(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $teacher = $this->createUser(['role' => 'teacher']);
        $course = $this->course($teacher->id);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_COURSE,
            'course_id' => $course->id, 'name' => 'Protected exception', 'code' => 'PROTECT1', 'slug' => 'protected-exception', 'status' => 'active',
        ]);
        $exception = AcademicCalendarException::query()->create([
            'course_id' => $course->id, 'classroom_id' => $classroom->id, 'created_by' => $teacher->id,
            'exception_date' => '2026-09-02', 'kind' => AcademicCalendarException::KIND_NO_CLASS,
            'title' => 'Class closure', 'reason' => 'This exception belongs to the classroom teacher workflow.',
        ]);

        $this->actingAs($admin)->delete(route('admin.calendar-exceptions.destroy', $exception))->assertNotFound();
        $this->assertDatabaseHas('academic_calendar_exceptions', ['id' => $exception->id]);
    }

    private function course(int $teacherId): Course
    {
        return Course::query()->create([
            'teacher_id' => $teacherId, 'name' => 'Phase 14 course', 'slug' => 'phase-14-'.str()->lower(str()->random(6)),
            'publication_status' => Course::PUBLICATION_PUBLISHED, 'is_active' => true,
            'starts_at' => '2026-09-01', 'ends_at' => '2026-09-30', 'schedule_days' => ['mon', 'wed'], 'study_time' => '08:00 - 10:00',
        ]);
    }
}
