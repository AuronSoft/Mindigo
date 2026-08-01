<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\Lesson;
use Tests\TestCase;

class TeacherCourseUiConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_course_index_uses_the_system_header_and_translated_actions(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $otherTeacher = User::factory()->create(['role' => 'teacher']);

        $course = $this->courseFor($teacher, 'My owned course');
        $this->courseFor($otherTeacher, 'Another teacher course');

        $this->actingAs($teacher)
            ->get(route('teacher.courses.index'))
            ->assertOk()
            ->assertSee(__('teacher-course::app.teaching_content'))
            ->assertSee(__('teacher-course::app.title'))
            ->assertSee(__('teacher-course::app.index_subtitle'))
            ->assertSee($course->name)
            ->assertDontSee('Another teacher course')
            ->assertSee(__('teacher-course::app.delete'))
            ->assertDontSee('teacher-course::app.delete');
    }

    public function test_course_pages_render_the_complete_three_level_system_header(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = $this->courseFor($teacher, 'Mindigo LMS');

        $pages = [
            [route('teacher.courses.create'), 'teacher-course::app.create_course_btn', 'teacher-course::app.create_subtitle'],
            [route('teacher.courses.edit', $course), 'teacher-course::app.edit_course', 'teacher-course::app.edit_subtitle'],
            [route('teacher.courses.show', $course), $course->name, 'teacher-course::app.detail_subtitle'],
        ];

        foreach ($pages as [$url, $title, $description]) {
            $response = $this->actingAs($teacher)->get($url);

            $response
                ->assertOk()
                ->assertSee(__('teacher-course::app.teaching_content'))
                ->assertSee(str_contains($title, '::') ? __($title) : $title)
                ->assertSee(__($description));
        }
    }

    public function test_lesson_pages_render_the_complete_three_level_system_header(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = $this->courseFor($teacher, 'Lesson test course');
        $chapter = Chapter::query()->create([
            'course_id' => $course->getKey(),
            'name' => 'First chapter',
            'sort_order' => 1,
        ]);
        $lesson = Lesson::query()->create([
            'chapter_id' => $chapter->getKey(),
            'name' => 'First lesson',
            'sort_order' => 1,
        ]);

        $pages = [
            [route('teacher.courses.lessons.create', [$course, $chapter]), 'teacher-course::app.add_lesson_title', 'teacher-course::app.lesson_create_subtitle'],
            [route('teacher.courses.lessons.edit', $lesson), 'teacher-course::app.edit_lesson_title', 'teacher-course::app.lesson_edit_subtitle'],
        ];

        foreach ($pages as [$url, $title, $description]) {
            $this->actingAs($teacher)
                ->get($url)
                ->assertOk()
                ->assertSee(__('teacher-course::app.lesson_content_title'))
                ->assertSee(__($title))
                ->assertSee(__($description));
        }
    }

    private function courseFor(User $teacher, string $name): Course
    {
        return Course::query()->create([
            'teacher_id' => $teacher->getKey(),
            'name' => $name,
            'slug' => str($name)->slug()->append('-', $teacher->getKey())->toString(),
            'description' => 'Course interface regression test.',
            'status' => 'active',
        ]);
    }
}
