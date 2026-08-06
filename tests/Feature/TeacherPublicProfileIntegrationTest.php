<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\TeacherProfile;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;
use Tests\TestCase;

class TeacherPublicProfileIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_only_displays_public_approved_and_provisioned_teachers(): void
    {
        $visible = $this->teacherWithPublicProfile('Nguyen Verified', 'Programming');
        $hidden = $this->createUser(['role' => 'teacher', 'name' => 'Hidden Teacher']);
        TeacherProfile::query()->create(['user_id' => $hidden->id, 'headline' => 'Hidden profile', 'specialization' => 'Programming', 'experience_years' => 2, 'is_public' => true]);

        $this->get(route('teachers.index', ['search' => 'Nguyen', 'specialization' => 'Programming']))
            ->assertOk()
            ->assertSee($visible->name)
            ->assertSee('Programming')
            ->assertDontSee($hidden->name);
    }

    public function test_public_profile_shows_verified_teacher_information_courses_and_social_links(): void
    {
        $teacher = $this->teacherWithPublicProfile('Tran Course Mentor', 'Data Science');
        $course = $this->courseFor($teacher, 'Data Analysis Foundation', ['enrollment_count' => 24, 'rating_average' => 4.7, 'rating_count' => 8]);

        $this->get(route('teachers.show', $teacher))
            ->assertOk()
            ->assertSee($teacher->name)
            ->assertSee('Đã xác minh')
            ->assertSee('Data Science')
            ->assertSee('Verified certificate')
            ->assertSee('https://example.com')
            ->assertSee($course->name)
            ->assertSee('24');
    }

    public function test_public_profile_rejects_unapproved_or_non_public_teachers(): void
    {
        $unapproved = $this->createUser(['role' => 'teacher']);
        TeacherProfile::query()->create(['user_id' => $unapproved->id, 'headline' => 'Public but unapproved', 'experience_years' => 1, 'is_public' => true]);

        $approvedButPrivate = $this->teacherWithPublicProfile('Private Teacher', 'Math');
        $approvedButPrivate->teacherProfile()->update(['is_public' => false]);

        $this->get(route('teachers.show', $unapproved))->assertNotFound();
        $this->get(route('teachers.show', $approvedButPrivate))->assertNotFound();
    }

    public function test_course_catalog_and_detail_link_to_verified_teacher_profile(): void
    {
        $teacher = $this->teacherWithPublicProfile('Catalog Mentor', 'Physics');
        $course = $this->courseFor($teacher, 'Physics Starter');

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee(route('teachers.show', $teacher), false)
            ->assertSee('Đã xác minh');

        $this->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertSee(route('teachers.show', $teacher), false)
            ->assertSee('Đã xác minh');
    }

    private function teacherWithPublicProfile(string $name, string $specialization): User
    {
        $teacher = $this->createUser(['role' => 'teacher', 'name' => $name]);

        TeacherProfile::query()->create([
            'user_id' => $teacher->id,
            'headline' => $specialization.' mentor',
            'biography' => 'Experienced teacher profile',
            'specialization' => $specialization,
            'experience_years' => 6,
            'qualifications' => ['Verified certificate'],
            'social_links' => ['website' => 'https://example.com'],
            'is_public' => true,
        ]);

        TeacherApplication::query()->create([
            'user_id' => $teacher->id,
            'application_code' => 'APP-'.str()->upper(str()->random(8)),
            'status' => TeacherApplication::STATUS_APPROVED,
            'teacher_provision_status' => TeacherApplication::PROVISION_ACTIVE,
            'application_type' => 'teacher',
            'full_name' => $teacher->name,
            'email' => $teacher->email,
            'phone' => '0900000000',
            'specialization' => $specialization,
            'teaching_mode' => 'online',
            'experience_years' => 6,
            'submitted_at' => now(),
            'provisioned_at' => now(),
        ]);

        return $teacher->refresh();
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
}
