<?php

namespace Tests\Feature;

use Database\Seeders\CourseMasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherCourse\Models\CourseCategory;
use Tests\TestCase;

class CourseMasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_shared_subjects(): void
    {
        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)->post(route('subjects.store'), [
            'name' => 'Khoa học máy tính', 'code' => 'CS', 'description' => 'Dùng chung toàn hệ thống.',
            'color' => 'green', 'status' => 'active', 'sort_order' => 1,
        ])->assertRedirect();

        $subject = Subject::query()->where('code', 'CS')->firstOrFail();
        $this->actingAs($admin)->put(route('subjects.update', $subject), [
            'name' => 'Tin học ứng dụng', 'code' => 'ICTA', 'description' => null,
            'color' => 'sky', 'status' => 'inactive', 'sort_order' => 2,
        ])->assertRedirect();
        $this->assertDatabaseHas('subjects', ['id' => $subject->id, 'code' => 'ICTA', 'status' => 'inactive']);

        $this->actingAs($admin)->delete(route('subjects.destroy', $subject))->assertRedirect(route('subjects.index'));
        $this->assertSoftDeleted('subjects', ['id' => $subject->id]);
    }

    public function test_admin_can_manage_course_categories_and_non_admin_is_forbidden(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $teacher = $this->createUser(['role' => 'teacher']);

        $this->actingAs($teacher)->get(route('admin.course-categories.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.course-categories.store'), [
            'name' => 'STEM', 'description' => 'Science and technology', 'is_active' => true, 'sort_order' => 3,
        ])->assertRedirect(route('admin.course-categories.index'));

        $category = CourseCategory::query()->where('slug', 'stem')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.course-categories.update', $category), [
            'name' => 'STEM nâng cao', 'description' => null, 'is_active' => false, 'sort_order' => 4,
        ])->assertRedirect(route('admin.course-categories.index'));
        $this->assertDatabaseHas('course_categories', ['id' => $category->id, 'is_active' => false, 'sort_order' => 4]);

        $this->actingAs($admin)->delete(route('admin.course-categories.destroy', $category))->assertRedirect();
        $this->assertDatabaseMissing('course_categories', ['id' => $category->id]);
    }

    public function test_course_form_only_receives_active_master_data(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $activeSubject = $this->subject('Active subject', 'ACTIVE', 'active');
        $inactiveSubject = $this->subject('Inactive subject', 'INACTIVE', 'inactive');
        $activeCategory = CourseCategory::query()->create(['name' => 'Active category', 'slug' => 'active-category', 'is_active' => true]);
        $inactiveCategory = CourseCategory::query()->create(['name' => 'Inactive category', 'slug' => 'inactive-category', 'is_active' => false]);

        $this->actingAs($teacher)->get(route('teacher.courses.create'))
            ->assertOk()
            ->assertSee('data-course-master-picker', false)
            ->assertSee(__('teacher-course::app.subject_search_placeholder'))
            ->assertSee(__('teacher-course::app.category_search_placeholder'))
            ->assertSee($activeSubject->name)
            ->assertDontSee($inactiveSubject->name)
            ->assertSee($activeCategory->name)
            ->assertDontSee($inactiveCategory->name);
    }

    public function test_course_rejects_inactive_subject_and_category(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $subject = $this->subject('Inactive subject', 'OFF', 'inactive');
        $category = CourseCategory::query()->create(['name' => 'Inactive category', 'slug' => 'inactive-category', 'is_active' => false]);

        $this->actingAs($teacher)->post(route('teacher.courses.store'), [
            'name' => 'Invalid master data', 'status' => 'active', 'difficulty' => 'beginner', 'language' => 'vi',
            'subject_id' => $subject->id, 'category_id' => $category->id,
        ])->assertSessionHasErrors(['subject_id', 'category_id']);
    }

    public function test_master_data_seeder_is_idempotent(): void
    {
        $this->seed(CourseMasterDataSeeder::class);
        $subjectCount = Subject::query()->count();
        $categoryCount = CourseCategory::query()->count();
        $this->seed(CourseMasterDataSeeder::class);

        $this->assertSame(60, $subjectCount);
        $this->assertSame(59, $categoryCount);
        $this->assertSame($subjectCount, Subject::query()->count());
        $this->assertSame($categoryCount, CourseCategory::query()->count());
    }

    public function test_course_duration_keeps_selected_unit_and_normalizes_minutes(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);

        $this->actingAs($teacher)->post(route('teacher.courses.store'), [
            'name' => 'Java trong 20 giờ', 'status' => 'active', 'difficulty' => 'beginner', 'language' => 'vi',
            'duration_value' => 20, 'duration_unit' => 'hour',
        ])->assertRedirect();

        $this->assertDatabaseHas('courses', [
            'teacher_id' => $teacher->id, 'duration_value' => 20, 'duration_unit' => 'hour',
            'estimated_duration_minutes' => 1200,
        ]);
    }

    private function subject(string $name, string $code, string $status): Subject
    {
        return Subject::query()->create([
            'name' => $name, 'code' => $code, 'slug' => str()->slug($name),
            'color' => 'green', 'status' => $status, 'sort_order' => 1,
        ]);
    }
}
