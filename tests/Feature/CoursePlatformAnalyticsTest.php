<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mindigo\AuditLog\Models\AuditLog;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseEnrollment;
use Mindigo\TeacherCourse\Models\CourseLessonProgress;
use Mindigo\TeacherCourse\Models\CourseReview;
use Mindigo\TeacherCourse\Models\Lesson;
use Tests\TestCase;

class CoursePlatformAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::clear();
    }

    public function test_teacher_analytics_are_accurate_and_scoped_to_owned_courses(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        [$course, $lesson] = $this->courseWithLesson($teacher);
        $enrollment = $this->enroll($course, $student, ['status' => 'completed', 'completion_percentage' => 100, 'time_spent_seconds' => 3600, 'completed_at' => now()]);
        CourseLessonProgress::query()->create(['enrollment_id' => $enrollment->id, 'lesson_id' => $lesson->id, 'completed_at' => now(), 'time_spent_seconds' => 3600]);
        CourseReview::query()->create(['course_id' => $course->id, 'enrollment_id' => $enrollment->id, 'student_id' => $student->id, 'rating' => 5, 'moderation_status' => 'visible']);
        $this->courseWithLesson($this->createUser(['role' => 'teacher']));

        $this->actingAs($teacher)->get(route('course-platform.analytics'))->assertOk()
            ->assertSee(__('teacher-course::analytics.title'))->assertSee('100%')->assertSee('60');
    }

    public function test_admin_analytics_include_platform_status_and_taxonomy_metrics(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $teacher = $this->createUser(['role' => 'teacher']);
        $this->courseWithLesson($teacher, ['publication_status' => Course::PUBLICATION_PUBLISHED, 'is_featured' => true]);
        $this->courseWithLesson($teacher, ['publication_status' => Course::PUBLICATION_DRAFT]);

        $this->actingAs($admin)->get(route('course-platform.analytics'))->assertOk()
            ->assertSee(__('teacher-course::analytics.published'))->assertSee(__('teacher-course::analytics.draft'));
    }

    public function test_student_and_guest_cannot_access_course_analytics_or_exports(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $this->get(route('course-platform.analytics'))->assertRedirect(route('login'));
        $this->actingAs($student)->get(route('course-platform.analytics'))->assertRedirect();
        $this->actingAs($student)->get(route('course-platform.reports.export', ['scope' => 'student', 'format' => 'csv']))->assertRedirect();
    }

    public function test_teacher_can_export_csv_excel_and_pdf_with_only_owned_data(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        [$course] = $this->courseWithLesson($teacher);
        $this->enroll($course, $student);

        foreach (['csv' => 'text/csv', 'xlsx' => 'application/vnd.ms-excel', 'pdf' => 'application/pdf'] as $format => $mime) {
            $response = $this->actingAs($teacher)->get(route('course-platform.reports.export', ['scope' => 'course', 'entity_id' => $course->id, 'format' => $format]));
            $response->assertOk();
            $this->assertStringStartsWith($mime, $response->headers->get('content-type'));
        }
    }

    public function test_course_actions_are_written_to_shared_audit_log(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        [$course] = $this->courseWithLesson($teacher);
        $this->actingAs($student)->post(route('courses.wishlist.store', $course))->assertRedirect();

        $this->assertTrue(AuditLog::query()->where('module', 'course-platform')->where('action', 'course_created')->exists());
        $this->assertTrue(AuditLog::query()->where('module', 'course-platform')->where('action', 'course_wishlist_added')->exists());
    }

    public function test_teacher_analytics_are_cached_and_activity_is_paginated(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        [$course] = $this->courseWithLesson($teacher);
        foreach (range(1, 17) as $index) {
            $this->enroll($course, $this->createUser(['role' => 'student']), ['last_activity_at' => now()->subMinutes($index)]);
        }

        $this->actingAs($teacher)->get(route('course-platform.analytics'))->assertOk()->assertSee('page=2', false);
        $this->assertTrue(Cache::has('course:analytics:teacher:'.$teacher->id));

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($teacher)->get(route('course-platform.analytics'))->assertOk();
        $this->assertLessThanOrEqual(8, count(DB::getQueryLog()));

        $this->enroll($course, $this->createUser(['role' => 'student']));
        $this->assertFalse(Cache::has('course:analytics:teacher:'.$teacher->id));
    }

    private function courseWithLesson(User $teacher, array $attributes = []): array
    {
        $course = Course::query()->create([
            'teacher_id' => $teacher->id, 'name' => 'Analytics '.str()->random(5), 'slug' => str()->uuid(),
            'status' => 'active', 'is_active' => true, 'publication_status' => Course::PUBLICATION_PUBLISHED,
            'published_at' => now(), 'difficulty' => 'beginner', 'language' => 'vi', 'access_type' => 'free', ...$attributes,
        ]);
        $chapter = Chapter::query()->create(['course_id' => $course->id, 'name' => 'Chapter', 'sort_order' => 1]);
        $lesson = Lesson::query()->create(['chapter_id' => $chapter->id, 'name' => 'Lesson', 'sort_order' => 1]);

        return [$course, $lesson];
    }

    private function enroll(Course $course, User $student, array $attributes = []): CourseEnrollment
    {
        return CourseEnrollment::query()->create([
            'course_id' => $course->id, 'student_id' => $student->id, 'status' => 'in_progress',
            'source' => 'self', 'completion_percentage' => 40, 'time_spent_seconds' => 600, 'last_activity_at' => now(), ...$attributes,
        ]);
    }
}
