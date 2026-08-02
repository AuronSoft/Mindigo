<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseEnrollment;
use Mindigo\TeacherCourse\Models\Lesson;
use Tests\TestCase;

class CoursePlatformFinalHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_archived_course_is_removed_from_student_workspace_and_lesson_access(): void
    {
        $student = $this->createUser(['role' => 'student']);
        [$course, $lesson] = $this->learningCourse($student);

        $course->update(['publication_status' => Course::PUBLICATION_ARCHIVED]);

        $this->actingAs($student)->get(route('student.courses.index'))
            ->assertOk()
            ->assertDontSee($course->name);
        $this->actingAs($student)->get(route('student.courses.show', $course->slug))->assertNotFound();
        $this->actingAs($student)
            ->get(route('student.courses.lessons.show', [$course->slug, $lesson->id]))
            ->assertNotFound();
        $this->actingAs($student)
            ->get(route('courses.lessons.show', [$course->slug, $lesson->id]))
            ->assertForbidden();
    }

    public function test_course_returned_to_review_is_not_available_until_published_again(): void
    {
        $student = $this->createUser(['role' => 'student']);
        [$course, $lesson] = $this->learningCourse($student);

        $course->update(['publication_status' => Course::PUBLICATION_PENDING_REVIEW]);

        $this->actingAs($student)->get(route('student.courses.show', $course->slug))->assertNotFound();
        $this->actingAs($student)
            ->get(route('courses.lessons.show', [$course->slug, $lesson->id]))
            ->assertForbidden();
    }

    public function test_soft_deleted_course_is_not_accessible_but_enrollment_keeps_historical_relation(): void
    {
        $student = $this->createUser(['role' => 'student']);
        [$course, $lesson, $enrollment] = $this->learningCourse($student);

        $course->delete();

        $this->actingAs($student)->get(route('student.courses.index'))
            ->assertOk()
            ->assertDontSee($course->name);
        $this->actingAs($student)->get(route('student.courses.show', $course->slug))->assertNotFound();
        $this->actingAs($student)
            ->get(route('courses.lessons.show', [$course->slug, $lesson->id]))
            ->assertNotFound();

        $this->assertTrue($enrollment->fresh()->course->trashed());
        $this->assertSame($course->name, $enrollment->fresh()->course->name);
    }

    public function test_student_cannot_use_another_course_slug_to_open_protected_content(): void
    {
        $student = $this->createUser(['role' => 'student']);
        [$ownedCourse] = $this->learningCourse($student);
        [, $foreignLesson] = $this->learningCourse($this->createUser(['role' => 'student']));

        $this->actingAs($student)
            ->get(route('courses.lessons.show', [$ownedCourse->slug, $foreignLesson->id]))
            ->assertNotFound();
    }

    private function learningCourse(User $student): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $course = Course::query()->create([
            'teacher_id' => $teacher->id,
            'name' => 'Hardening '.str()->random(8),
            'slug' => 'hardening-'.str()->lower(str()->random(10)),
            'status' => 'active',
            'is_active' => true,
            'publication_status' => Course::PUBLICATION_PUBLISHED,
            'published_at' => now(),
            'difficulty' => 'beginner',
            'language' => 'vi',
            'access_type' => 'free',
        ]);
        $chapter = Chapter::query()->create(['course_id' => $course->id, 'name' => 'Hardening chapter']);
        $lesson = Lesson::query()->create([
            'chapter_id' => $chapter->id,
            'name' => 'Protected lesson',
            'content' => 'Protected content',
        ]);
        $enrollment = CourseEnrollment::query()->create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'status' => CourseEnrollment::STATUS_ENROLLED,
            'source' => 'self',
            'enrolled_at' => now(),
        ]);

        return [$course, $lesson, $enrollment];
    }
}
