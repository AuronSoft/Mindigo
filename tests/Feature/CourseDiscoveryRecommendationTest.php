<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseEnrollment;
use Mindigo\TeacherCourse\Models\CourseView;
use Mindigo\TeacherCourse\Services\CourseDiscoveryService;
use Mindigo\TeacherCourse\Services\CourseRecommendationService;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CourseDiscoveryRecommendationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::clear();
    }

    public function test_student_can_add_and_remove_a_unique_wishlist_item(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course();

        $this->actingAs($student)->post(route('courses.wishlist.store', $course))->assertRedirect();
        $this->actingAs($student)->post(route('courses.wishlist.store', $course))->assertRedirect();
        $this->assertDatabaseCount('course_wishlists', 1);

        $this->actingAs($student)->get(route('student.wishlist.index'))->assertOk()->assertSee($course->name);
        $this->actingAs($student)->delete(route('courses.wishlist.destroy', $course))->assertRedirect();
        $this->assertDatabaseCount('course_wishlists', 0);
    }

    public function test_wishlist_is_protected_and_private_courses_are_rejected(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $draft = $this->course(['publication_status' => Course::PUBLICATION_DRAFT]);

        $this->actingAs($teacher)->post(route('courses.wishlist.store', $draft))->assertRedirect();
        $this->assertDatabaseCount('course_wishlists', 0);
        Auth::logout();
        $this->get(route('student.wishlist.index'))->assertRedirect(route('login'));
    }

    public function test_course_detail_records_recent_views_without_duplicate_rows(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course();

        $this->actingAs($student)->get(route('courses.show', $course->slug))->assertOk();
        $this->actingAs($student)->get(route('courses.show', $course->slug))->assertOk();

        $this->assertDatabaseCount('course_views', 1);
        $this->assertSame(2, CourseView::query()->firstOrFail()->view_count);
        $this->actingAs($student)->get(route('student.courses.recent'))->assertOk()->assertSee($course->name);
    }

    public function test_related_courses_use_shared_metadata_and_hide_unpublished_courses(): void
    {
        $subject = $this->subject('Mathematics');
        $course = $this->course(['subject_id' => $subject->id]);
        $related = $this->course(['name' => 'Related mathematics', 'subject_id' => $subject->id]);
        $this->course(['name' => 'Hidden mathematics', 'subject_id' => $subject->id, 'publication_status' => Course::PUBLICATION_DRAFT]);

        $result = app(CourseDiscoveryService::class)->related($course);
        $this->assertTrue($result->contains($related));
        $this->assertFalse($result->contains('name', 'Hidden mathematics'));
    }

    public function test_recommendations_follow_enrollment_history_and_exclude_enrolled_course(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $subject = $this->subject('Physics');
        $history = $this->course(['name' => 'Physics history', 'subject_id' => $subject->id]);
        $recommended = $this->course(['name' => 'Physics next', 'subject_id' => $subject->id]);
        $this->course(['name' => 'Unrelated']);
        CourseEnrollment::query()->create(['course_id' => $history->id, 'student_id' => $student->id, 'status' => 'enrolled', 'source' => 'self']);

        $result = app(CourseRecommendationService::class)->forStudent($student);
        $this->assertSame($recommended->id, $result->first()->id);
        $this->assertFalse($result->contains($history));
    }

    public function test_only_admin_can_manage_featured_courses_and_home_displays_them(): void
    {
        $course = $this->course();
        $student = $this->createUser(['role' => 'student']);
        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($student)->patch(route('admin.courses.featured', $course), ['is_featured' => true])->assertForbidden();
        $this->actingAs($admin)->patch(route('admin.courses.featured', $course), ['is_featured' => true, 'featured_order' => 1])->assertRedirect();
        $this->assertTrue($course->refresh()->is_featured);
        $this->get(route('home'))->assertOk()->assertSee($course->name);
    }

    public function test_search_suggestions_history_and_seo_are_publication_safe(): void
    {
        $course = $this->course(['name' => 'Algebra Foundation']);
        $this->course(['name' => 'Algebra Hidden', 'publication_status' => Course::PUBLICATION_DRAFT]);

        $this->getJson(route('courses.search.suggestions', ['query' => 'Algebra']))
            ->assertOk()->assertJsonFragment(['Algebra Foundation'])->assertJsonMissing(['Algebra Hidden']);
        $this->get(route('courses.index', ['search' => 'algebra']))->assertOk()
            ->assertSee('canonical')->assertSee('og:title', false);
        $this->assertDatabaseHas('course_searches', ['keyword' => 'algebra']);
        $this->assertSame($course->id, Course::query()->where('name', 'Algebra Foundation')->value('id'));
    }

    private function course(array $attributes = []): Course
    {
        $teacher = $attributes['teacher_id'] ?? $this->createUser(['role' => 'teacher'])->id;
        $name = $attributes['name'] ?? 'Course '.str()->random(6);

        return Course::query()->create([
            'teacher_id' => $teacher, 'name' => $name, 'slug' => str($name)->slug().'-'.str()->lower(str()->random(5)),
            'status' => 'active', 'is_active' => true, 'publication_status' => Course::PUBLICATION_PUBLISHED,
            'published_at' => now(), 'difficulty' => 'beginner', 'education_level' => 'general',
            'language' => 'vi', 'access_type' => 'free', ...$attributes,
        ]);
    }

    private function subject(string $name): Subject
    {
        return Subject::query()->create(['name' => $name, 'code' => str($name)->slug()->upper(), 'slug' => str($name)->slug(), 'status' => 'active']);
    }
}
