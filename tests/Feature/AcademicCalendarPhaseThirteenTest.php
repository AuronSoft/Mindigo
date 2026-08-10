<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherCourse\Models\Course;
use Tests\TestCase;

class AcademicCalendarPhaseThirteenTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_class_exception_is_skipped_when_generating_course_sessions(): void
    {
        [$teacher, $classroom] = $this->courseClassroom('2026-09-30');

        $this->actingAs($teacher)->post(route('teacher.classrooms.calendar-exceptions.store', $classroom), [
            'exception_date' => '2026-09-02', 'title' => 'Nghỉ lễ', 'reason' => 'Nhà trường thông báo nghỉ lễ theo kế hoạch năm học.',
        ])->assertRedirect();

        $this->actingAs($teacher)->post(route('teacher.classrooms.schedules.generate-course-plan', $classroom), [
            'start_date' => '2026-09-01', 'session_count' => 2,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('classroom_schedules', ['classroom_id' => $classroom->id, 'session_date' => '2026-09-02 00:00:00']);
        $this->assertDatabaseHas('classroom_schedules', ['classroom_id' => $classroom->id, 'session_date' => '2026-09-07 00:00:00']);
        $this->assertDatabaseHas('classroom_schedules', ['classroom_id' => $classroom->id, 'session_date' => '2026-09-09 00:00:00']);
    }

    public function test_generation_stops_at_course_end_and_exception_is_owner_scoped(): void
    {
        [$teacher, $classroom] = $this->courseClassroom('2026-09-08');
        $other = $this->createUser(['role' => 'teacher']);

        $this->actingAs($other)->post(route('teacher.classrooms.calendar-exceptions.store', $classroom), [
            'exception_date' => '2026-09-02', 'title' => 'Không hợp lệ', 'reason' => 'Không có quyền tạo ngoại lệ cho lớp học này.',
        ])->assertForbidden();

        $this->actingAs($teacher)->post(route('teacher.classrooms.calendar-exceptions.store', $classroom), [
            'exception_date' => '2026-09-02', 'title' => 'Nghỉ chuyên môn', 'reason' => 'Giáo viên tham gia sinh hoạt chuyên môn toàn trường.',
        ])->assertRedirect();
        $this->actingAs($teacher)->post(route('teacher.classrooms.schedules.generate-course-plan', $classroom), [
            'start_date' => '2026-09-01', 'session_count' => 2,
        ])->assertSessionHasErrors('session_count');

        $this->assertDatabaseCount('classroom_schedules', 0);
    }

    private function courseClassroom(string $endsAt): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $course = Course::query()->create([
            'teacher_id' => $teacher->id, 'name' => 'Khóa có ngoại lệ', 'slug' => 'khoa-ngoai-le-'.str()->lower(str()->random(5)),
            'publication_status' => Course::PUBLICATION_PUBLISHED, 'is_active' => true,
            'starts_at' => '2026-09-01', 'ends_at' => $endsAt, 'schedule_days' => ['mon', 'wed'], 'study_time' => '08:00 - 10:00',
        ]);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_COURSE,
            'course_id' => $course->id, 'name' => 'Lớp ngoại lệ', 'code' => str()->upper(str()->random(8)), 'slug' => 'lop-'.str()->lower(str()->random(8)), 'status' => 'active',
        ]);

        return [$teacher, $classroom];
    }
}
