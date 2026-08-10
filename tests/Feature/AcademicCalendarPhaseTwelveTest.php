<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\Lesson;
use Tests\TestCase;

class AcademicCalendarPhaseTwelveTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_generates_an_idempotent_course_session_plan_with_lessons(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $course = Course::query()->create([
            'teacher_id' => $teacher->id, 'name' => 'Toán theo kế hoạch', 'slug' => 'toan-theo-ke-hoach',
            'publication_status' => Course::PUBLICATION_PUBLISHED, 'is_active' => true,
            'starts_at' => '2026-09-01', 'schedule_days' => ['mon', 'wed'], 'study_time' => '08:00 - 10:00',
            'duration_value' => 4, 'duration_unit' => 'session',
        ]);
        $chapter = Chapter::query()->create(['course_id' => $course->id, 'name' => 'Chương 1', 'sort_order' => 1]);
        $firstLesson = Lesson::query()->create(['chapter_id' => $chapter->id, 'name' => 'Bài học thứ nhất', 'sort_order' => 1]);
        Lesson::query()->create(['chapter_id' => $chapter->id, 'name' => 'Bài học thứ hai', 'sort_order' => 2]);
        $classroom = $this->courseClassroom($teacher->id, $course->id);
        $payload = ['start_date' => '2026-09-01', 'session_count' => 4];

        $this->actingAs($teacher)->post(route('teacher.classrooms.schedules.generate-course-plan', $classroom), $payload)->assertRedirect();

        $this->assertDatabaseCount('classroom_schedules', 4);
        $this->assertDatabaseHas('classroom_schedules', [
            'classroom_id' => $classroom->id, 'lesson_id' => $firstLesson->id, 'title' => 'Bài học thứ nhất',
            'session_date' => '2026-09-02 00:00:00', 'start_time' => '08:00', 'end_time' => '10:00',
        ]);
        $this->assertDatabaseHas('classroom_schedules', ['classroom_id' => $classroom->id, 'session_date' => '2026-09-07 00:00:00']);

        $this->actingAs($teacher)->post(route('teacher.classrooms.schedules.generate-course-plan', $classroom), $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();
        $this->assertDatabaseCount('classroom_schedules', 4);
    }

    public function test_standalone_class_and_non_owner_cannot_generate_a_course_plan(): void
    {
        $owner = $this->createUser(['role' => 'teacher']);
        $other = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create([
            'created_by' => $owner->id, 'teacher_id' => $owner->id, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Lớp độc lập', 'code' => 'PLAN12', 'slug' => 'plan-12', 'status' => 'active',
        ]);
        $payload = ['start_date' => '2026-09-01', 'session_count' => 4];

        $this->actingAs($other)->post(route('teacher.classrooms.schedules.generate-course-plan', $classroom), $payload)->assertForbidden();
        $this->actingAs($owner)->post(route('teacher.classrooms.schedules.generate-course-plan', $classroom), $payload)->assertSessionHasErrors('session_count');
        $this->assertDatabaseCount('classroom_schedules', 0);
    }

    private function courseClassroom(int $teacherId, int $courseId): Classroom
    {
        return Classroom::query()->create([
            'created_by' => $teacherId, 'teacher_id' => $teacherId, 'type' => Classroom::TYPE_COURSE,
            'course_id' => $courseId, 'name' => 'Lớp theo khóa', 'code' => 'COURSE12', 'slug' => 'course-12', 'status' => 'active',
        ]);
    }
}
