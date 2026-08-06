<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Mindigo\Notification\Notifications\TeacherApplicationSubmitted;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherCourse\Models\CourseCategory;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;
use Tests\TestCase;

class TeacherTutorRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_and_submit_teacher_application(): void
    {
        $this->seedMasterData();

        $this->get(route('teacher-applications.create'))
            ->assertOk()
            ->assertSee(__('teacher-onboarding::application.title'));

        $this->post(route('teacher-applications.store'), $this->payload())
            ->assertRedirect(route('teacher-applications.create'));

        $this->assertDatabaseHas('teacher_applications', [
            'email' => 'candidate@mindigo.test',
            'status' => TeacherApplication::STATUS_SUBMITTED,
            'application_type' => 'teacher',
        ]);
    }

    public function test_authenticated_user_is_prefilled_and_can_submit(): void
    {
        $this->seedMasterData();
        $user = $this->createUser([
            'role' => 'student',
            'name' => 'Nguyen Applicant',
            'email' => 'applicant@mindigo.test',
            'phone' => '0909000000',
        ]);

        $this->actingAs($user)
            ->get(route('teacher-applications.create'))
            ->assertOk()
            ->assertSee('Nguyen Applicant')
            ->assertSee('applicant@mindigo.test');

        $this->actingAs($user)
            ->post(route('teacher-applications.store'), $this->payload([
                'email' => 'applicant@mindigo.test',
            ]))
            ->assertRedirect(route('teacher-applications.create'));

        $this->assertDatabaseHas('teacher_applications', [
            'user_id' => $user->id,
            'email' => 'applicant@mindigo.test',
        ]);
    }

    public function test_validation_rejects_invalid_required_fields_and_video_url(): void
    {
        $this->post(route('teacher-applications.store'), [
            'email' => 'not-an-email',
            'intro_video_url' => 'https://example.com/video',
        ])->assertSessionHasErrors([
            'full_name',
            'email',
            'phone',
            'application_type',
            'specialization',
            'teaching_mode',
            'experience_years',
            'teaching_method',
            'intro_video_url',
            'terms_accepted',
        ]);
    }

    public function test_documents_are_uploaded_to_private_storage(): void
    {
        $disk = Storage::fake('local');
        $this->seedMasterData();

        $this->post(route('teacher-applications.store'), $this->payload([
            'identity_document' => UploadedFile::fake()->image('identity.jpg'),
            'cv_document' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]))->assertRedirect(route('teacher-applications.create'));

        $application = TeacherApplication::query()->firstOrFail();

        $disk->assertExists($application->verification_documents['identity']['path']);
        $disk->assertExists($application->verification_documents['cv']['path']);
    }

    public function test_active_duplicate_application_is_blocked(): void
    {
        $this->seedMasterData();
        TeacherApplication::query()->create([
            ...$this->payload(),
            'application_code' => 'TA-260806-ABC123',
            'status' => TeacherApplication::STATUS_SUBMITTED,
            'verification_documents' => [],
            'submitted_at' => now(),
        ]);

        $this->post(route('teacher-applications.store'), $this->payload())
            ->assertForbidden();
    }

    public function test_teacher_cannot_create_application(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);

        $this->actingAs($teacher)
            ->post(route('teacher-applications.store'), $this->payload())
            ->assertForbidden();
    }

    public function test_admin_receives_on_app_notification(): void
    {
        Notification::fake();
        $this->seedMasterData();
        $admin = $this->createUser(['role' => 'admin', 'is_active' => true]);

        $this->post(route('teacher-applications.store'), $this->payload())
            ->assertRedirect(route('teacher-applications.create'));

        Notification::assertSentTo($admin, TeacherApplicationSubmitted::class);
    }

    public function test_localized_page_renders_in_english(): void
    {
        app()->setLocale('en');

        $this->get(route('teacher-applications.create'))
            ->assertOk()
            ->assertSee('Teacher/Tutor Registration');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Nguyen Candidate',
            'email' => 'candidate@mindigo.test',
            'phone' => '0909123456',
            'application_type' => 'teacher',
            'subject_id' => Subject::query()->value('id'),
            'category_id' => CourseCategory::query()->value('id'),
            'education_level' => 'upper_secondary',
            'specialization' => 'Mathematics and exam preparation',
            'teaching_mode' => 'online',
            'experience_years' => 3,
            'teaching_method' => 'I use structured lessons, short checks, and feedback after every session.',
            'intro_video_url' => 'https://youtube.com/watch?v=abc123',
            'terms_accepted' => '1',
        ], $overrides);
    }

    private function seedMasterData(): void
    {
        Subject::query()->create([
            'name' => 'Mathematics',
            'code' => 'MATH',
            'slug' => 'mathematics',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        CourseCategory::query()->create([
            'name' => 'Exam preparation',
            'slug' => 'exam-preparation',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }
}
