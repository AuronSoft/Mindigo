<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\Lesson;
use Tests\TestCase;

class AuthenticatedCourseDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_the_original_course_before_login(): void
    {
        $course = $this->course();
        $url = route('courses.show', $course->slug);

        $this->get($url)
            ->assertOk()
            ->assertSee($course->name);
    }

    public function test_student_can_view_complete_published_course_information(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course([
            'learning_outcomes' => ['Solve linear equations'],
            'requirements' => ['Basic algebra'],
            'target_learners' => ['High-school students'],
        ]);
        $chapter = Chapter::query()->create(['course_id' => $course->id, 'name' => 'Foundations']);
        Lesson::query()->create(['chapter_id' => $chapter->id, 'name' => 'First lesson', 'is_preview' => true]);

        $this->actingAs($student)->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertSee($course->name)
            ->assertSee('Solve linear equations')
            ->assertSee('Basic algebra')
            ->assertSee('High-school students')
            ->assertSee('Foundations')
            ->assertSee('First lesson');
    }

    public function test_owner_and_admin_can_preview_an_unpublished_course(): void
    {
        $owner = $this->createUser(['role' => 'teacher']);
        $admin = $this->createUser(['role' => 'admin']);
        $course = $this->course([
            'teacher_id' => $owner->id,
            'publication_status' => Course::PUBLICATION_DRAFT,
            'published_at' => null,
        ]);

        $this->actingAs($owner)->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertSee(__('teacher-course::catalog.preview_mode'))
            ->assertSee(route('courses.index'), false)
            ->assertSee('mindigo', false);

        $this->actingAs($owner)->get(route('courses.show', ['course' => $course->slug, 'from' => 'teacher']))
            ->assertOk()
            ->assertSee(route('teacher.courses.show', $course), false);

        $this->actingAs($admin)->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertSee($course->name)
            ->assertSee(route('courses.index'), false);
    }

    public function test_student_and_non_owner_teacher_cannot_open_unpublished_course(): void
    {
        $course = $this->course(['publication_status' => Course::PUBLICATION_UNLISTED]);

        foreach (['student', 'teacher'] as $role) {
            $this->actingAs($this->createUser(['role' => $role]))
                ->get(route('courses.show', $course->slug))
                ->assertNotFound();
        }
    }

    public function test_student_can_only_open_preview_lessons(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course();
        $preview = $this->lesson($course, true);
        $protected = $this->lesson($course, false);

        $this->actingAs($student)
            ->get(route('courses.lessons.show', [$course->slug, $preview->id]))
            ->assertOk()
            ->assertSee($preview->name);

        $this->actingAs($student)
            ->get(route('courses.lessons.show', [$course->slug, $protected->id]))
            ->assertForbidden();
    }

    public function test_owner_and_admin_can_open_every_lesson_in_preview_mode(): void
    {
        $owner = $this->createUser(['role' => 'teacher']);
        $course = $this->course(['teacher_id' => $owner->id, 'publication_status' => Course::PUBLICATION_DRAFT]);
        $lesson = $this->lesson($course, false);

        $this->actingAs($owner)->get(route('courses.lessons.show', [$course->slug, $lesson->id]))->assertOk();
        $this->actingAs($this->createUser(['role' => 'admin']))
            ->get(route('courses.lessons.show', [$course->slug, $lesson->id]))
            ->assertOk();
    }

    public function test_lesson_cannot_be_accessed_through_another_course_url(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $lesson = $this->lesson($this->course(), true);
        $otherCourse = $this->course();

        $this->actingAs($student)
            ->get(route('courses.lessons.show', [$otherCourse->slug, $lesson->id]))
            ->assertNotFound();
    }

    public function test_video_and_attachments_are_protected_by_lesson_permission(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('course-content/videos/lesson.mp4', 'video');
        Storage::disk('local')->put('course-content/attachments/guide.pdf', 'document');

        $course = $this->course();
        $lesson = $this->lesson($course, true, [
            'video_path' => 'course-content/videos/lesson.mp4',
            'attachment_paths' => [[
                'path' => 'course-content/attachments/guide.pdf',
                'disk' => 'local',
                'original_name' => 'guide.pdf',
                'mime' => 'application/pdf',
            ]],
        ]);
        $student = $this->createUser(['role' => 'student']);

        $this->get(route('courses.lessons.video', [$course->slug, $lesson->id]))->assertRedirect(route('login'));
        $this->actingAs($student)->get(route('courses.lessons.video', [$course->slug, $lesson->id]))->assertOk();
        $this->actingAs($student)->get(route('courses.lessons.attachments.show', [$course->slug, $lesson->id, 0]))->assertOk();

        $lesson->update(['is_preview' => false]);
        $this->actingAs($student)->get(route('courses.lessons.video', [$course->slug, $lesson->id]))->assertForbidden();
        $this->actingAs($student)->get(route('courses.lessons.attachments.show', [$course->slug, $lesson->id, 0]))->assertForbidden();
    }

    private function course(array $attributes = []): Course
    {
        $teacherId = $attributes['teacher_id'] ?? $this->createUser(['role' => 'teacher'])->id;

        return Course::query()->create([
            'teacher_id' => $teacherId,
            'name' => 'Course '.str()->random(8),
            'slug' => 'course-'.str()->lower(str()->random(10)),
            'status' => 'active',
            'is_active' => true,
            'publication_status' => Course::PUBLICATION_PUBLISHED,
            'published_at' => now(),
            'difficulty' => 'beginner',
            'education_level' => 'upper_secondary',
            'language' => 'vi',
            'access_type' => 'free',
            ...$attributes,
        ]);
    }

    private function lesson(Course $course, bool $preview, array $attributes = []): Lesson
    {
        $chapter = Chapter::query()->create([
            'course_id' => $course->id,
            'name' => 'Chapter '.str()->random(5),
        ]);

        return Lesson::query()->create([
            'chapter_id' => $chapter->id,
            'name' => 'Lesson '.str()->random(5),
            'content' => 'Protected lesson content',
            'is_preview' => $preview,
            ...$attributes,
        ]);
    }
}
