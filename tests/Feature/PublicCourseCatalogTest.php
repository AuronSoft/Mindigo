<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseCategory;
use Tests\TestCase;

class PublicCourseCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_catalog_only_displays_active_published_courses(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $published = $this->courseFor($teacher, 'Published course');
        $this->courseFor($teacher, 'Draft course', ['publication_status' => Course::PUBLICATION_DRAFT]);
        $this->courseFor($teacher, 'Pending course', ['publication_status' => Course::PUBLICATION_PENDING_REVIEW]);
        $this->courseFor($teacher, 'Unlisted course', ['publication_status' => Course::PUBLICATION_UNLISTED]);
        $this->courseFor($teacher, 'Archived course', ['publication_status' => Course::PUBLICATION_ARCHIVED]);
        $this->courseFor($teacher, 'Inactive published course', ['is_active' => false, 'status' => 'inactive']);

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee($published->name)
            ->assertDontSee('Draft course')
            ->assertDontSee('Pending course')
            ->assertDontSee('Unlisted course')
            ->assertDontSee('Archived course')
            ->assertDontSee('Inactive published course');
    }

    public function test_catalog_searches_course_teacher_subject_and_category(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'name' => 'Nguyen Minh Teacher']);
        $subject = $this->subject('Physics');
        $category = $this->category('Exam preparation');
        $course = $this->courseFor($teacher, 'Mechanics foundation', [
            'subject_id' => $subject->id,
            'category_id' => $category->id,
        ]);
        $this->courseFor(User::factory()->create(['role' => 'teacher']), 'Literature writing');

        foreach (['Mechanics', 'Nguyen Minh', 'Physics', 'Exam preparation'] as $search) {
            $this->get(route('courses.index', ['search' => $search]))
                ->assertOk()
                ->assertSee($course->name)
                ->assertDontSee('Literature writing');
        }
    }

    public function test_catalog_filters_by_subject_category_education_level_and_difficulty(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $subject = $this->subject('Mathematics');
        $category = $this->category('High school');
        $matching = $this->courseFor($teacher, 'Advanced algebra', [
            'subject_id' => $subject->id,
            'category_id' => $category->id,
            'education_level' => 'upper_secondary',
            'difficulty' => 'advanced',
        ]);
        $this->courseFor($teacher, 'Basic English', ['education_level' => 'primary', 'difficulty' => 'beginner']);

        $this->get(route('courses.index', [
            'subject_id' => $subject->id,
            'category_id' => $category->id,
            'education_level' => 'upper_secondary',
            'difficulty' => 'advanced',
        ]))->assertOk()->assertSee($matching->name)->assertDontSee('Basic English');
    }

    public function test_catalog_sort_options_use_real_aggregate_columns(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $newest = $this->courseFor($teacher, 'Newest and enrolled', [
            'published_at' => now(),
            'view_count' => 10,
            'enrollment_count' => 100,
            'rating_average' => 3.5,
            'rating_count' => 10,
        ]);
        $popular = $this->courseFor($teacher, 'Popular and rated', [
            'published_at' => now()->subDay(),
            'view_count' => 500,
            'enrollment_count' => 20,
            'rating_average' => 4.9,
            'rating_count' => 80,
        ]);

        $this->assertSorted('newest', $newest, $popular);
        $this->assertSorted('popular', $popular, $newest);
        $this->assertSorted('rating', $popular, $newest);
        $this->assertSorted('enrolled', $newest, $popular);
    }

    public function test_course_detail_requires_login_and_returns_to_the_same_course_after_login(): void
    {
        $student = User::factory()->create(['role' => 'student', 'password' => 'password']);
        $course = $this->courseFor(User::factory()->create(['role' => 'teacher']), 'Login protected course');
        $detailUrl = route('courses.show', $course->slug);

        $this->get($detailUrl)->assertRedirect(route('login'));
        $this->assertSame($detailUrl, session('url.intended'));

        $this->post(route('login.store'), ['email' => $student->email, 'password' => 'password'])
            ->assertRedirect($detailUrl);

        $this->get($detailUrl)->assertOk()->assertSee($course->name);
    }

    public function test_unpublished_course_detail_is_not_visible_to_authenticated_users(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = $this->courseFor(User::factory()->create(['role' => 'teacher']), 'Hidden draft', [
            'publication_status' => Course::PUBLICATION_DRAFT,
        ]);

        $this->actingAs($student)->get(route('courses.show', $course->slug))->assertNotFound();
    }

    public function test_catalog_is_paginated_and_preserves_filters(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        foreach (range(1, 13) as $index) {
            $this->courseFor($teacher, "Course {$index}");
        }

        $this->get(route('courses.index', ['search' => 'Course']))
            ->assertOk()
            ->assertViewHas('courses', function ($courses): bool {
                return $courses->total() === 13
                    && $courses->count() === 12
                    && str_contains($courses->nextPageUrl(), 'search=Course');
            });
    }

    public function test_homepage_explore_action_links_to_the_catalog(): void
    {
        $this->get(route('home'))->assertOk()->assertSee(route('courses.index'), false);
    }

    private function assertSorted(string $sort, Course $first, Course $second): void
    {
        $this->get(route('courses.index', ['sort' => $sort]))
            ->assertOk()
            ->assertSeeInOrder([$first->name, $second->name]);
    }

    private function courseFor(User $teacher, string $name, array $attributes = []): Course
    {
        return Course::query()->create([
            'teacher_id' => $teacher->id,
            'name' => $name,
            'slug' => str($name)->slug().'-'.str()->lower(str()->random(6)),
            'status' => 'active',
            'is_active' => true,
            'publication_status' => Course::PUBLICATION_PUBLISHED,
            'published_at' => now(),
            'difficulty' => 'beginner',
            'language' => 'vi',
            'access_type' => 'free',
            ...$attributes,
        ]);
    }

    private function subject(string $name): Subject
    {
        return Subject::query()->create([
            'name' => $name,
            'code' => str($name)->slug()->upper(),
            'slug' => str($name)->slug(),
            'status' => 'active',
        ]);
    }

    private function category(string $name): CourseCategory
    {
        return CourseCategory::query()->create(['name' => $name, 'slug' => str($name)->slug(), 'is_active' => true]);
    }
}
