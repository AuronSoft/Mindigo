<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Mindigo\Notification\Notifications\TeacherApplicationDecision;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherCourse\Models\CourseCategory;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;
use Tests\TestCase;

class TeacherApplicationAdminReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_filter_and_paginate_teacher_applications(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $math = $this->seedMasterData();
        $this->application(['full_name' => 'Nguyen Math Tutor', 'subject_id' => $math['subject']->id]);
        $this->application([
            'full_name' => 'Tran English Teacher',
            'email' => 'english@mindigo.test',
            'application_code' => 'TA-260806-ENG001',
            'status' => TeacherApplication::STATUS_SCREENING,
            'application_type' => 'teacher',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.teacher-applications.index', [
                'search' => 'Math',
                'status' => TeacherApplication::STATUS_SUBMITTED,
                'application_type' => 'tutor',
            ]))
            ->assertOk()
            ->assertSee('Nguyen Math Tutor')
            ->assertDontSee('Tran English Teacher');
    }

    public function test_admin_can_view_detail_and_download_private_document(): void
    {
        Storage::fake('local');
        $admin = $this->createUser(['role' => 'admin']);
        Storage::disk('local')->put('teacher-applications/test/cv.pdf', 'cv-content');
        $application = $this->application([
            'verification_documents' => [
                'cv' => [
                    'disk' => 'local',
                    'path' => 'teacher-applications/test/cv.pdf',
                    'name' => 'cv.pdf',
                ],
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.teacher-applications.show', $application))
            ->assertOk()
            ->assertSee($application->full_name)
            ->assertSee('cv');

        $this->actingAs($admin)
            ->get(URL::temporarySignedRoute('admin.teacher-applications.documents.show', now()->addMinutes(5), [$application, 'cv']))
            ->assertOk();
    }

    public function test_valid_status_transitions_are_saved_with_internal_note(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $application = $this->application();

        $this->actingAs($admin)
            ->patch(route('admin.teacher-applications.update', $application), [
                'status' => TeacherApplication::STATUS_SCREENING,
                'internal_note' => 'Verified identity and experience summary.',
            ])
            ->assertRedirect(route('admin.teacher-applications.show', $application));

        $this->assertDatabaseHas('teacher_applications', [
            'id' => $application->id,
            'status' => TeacherApplication::STATUS_SCREENING,
            'reviewed_by' => $admin->id,
            'internal_note' => 'Verified identity and experience summary.',
        ]);
    }

    public function test_need_more_info_and_rejected_notify_the_applicant(): void
    {
        Notification::fake();
        $admin = $this->createUser(['role' => 'admin']);
        $applicant = $this->createUser(['role' => 'student']);
        $application = $this->application([
            'user_id' => $applicant->id,
            'status' => TeacherApplication::STATUS_SCREENING,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.teacher-applications.update', $application), [
                'status' => TeacherApplication::STATUS_NEED_MORE_INFO,
                'status_note' => 'Please upload your latest degree.',
            ])
            ->assertRedirect(route('admin.teacher-applications.show', $application));

        Notification::assertSentTo($applicant, TeacherApplicationDecision::class);
    }

    public function test_invalid_transition_and_missing_public_note_are_rejected(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $application = $this->application();

        $this->actingAs($admin)
            ->patch(route('admin.teacher-applications.update', $application), [
                'status' => TeacherApplication::STATUS_NEED_MORE_INFO,
            ])
            ->assertSessionHasErrors('status_note');

        $this->actingAs($admin)
            ->patch(route('admin.teacher-applications.update', $application), [
                'status' => TeacherApplication::STATUS_APPROVED,
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_non_admin_cannot_access_admin_review_workspace(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $application = $this->application();

        $this->actingAs($student)
            ->get(route('admin.teacher-applications.index'))
            ->assertForbidden();

        $this->actingAs($student)
            ->get(route('admin.teacher-applications.show', $application))
            ->assertForbidden();
    }

    private function application(array $overrides = []): TeacherApplication
    {
        $masterData = $this->seedMasterData();

        return TeacherApplication::query()->create(array_merge([
            'application_code' => 'TA-260806-'.fake()->unique()->bothify('???###'),
            'status' => TeacherApplication::STATUS_SUBMITTED,
            'application_type' => 'tutor',
            'full_name' => 'Nguyen Candidate',
            'email' => fake()->unique()->safeEmail(),
            'phone' => '0909123456',
            'subject_id' => $masterData['subject']->id,
            'category_id' => $masterData['category']->id,
            'education_level' => 'upper_secondary',
            'specialization' => 'Mathematics and exam preparation',
            'teaching_mode' => 'online',
            'experience_years' => 3,
            'verification_documents' => [],
            'teaching_method' => 'Structured lessons and weekly feedback.',
            'submitted_at' => now(),
        ], $overrides));
    }

    private function seedMasterData(): array
    {
        $subject = Subject::query()->firstOrCreate(
            ['slug' => 'mathematics'],
            ['name' => 'Mathematics', 'code' => 'MATH', 'status' => 'active', 'sort_order' => 1]
        );

        $category = CourseCategory::query()->firstOrCreate(
            ['slug' => 'exam-preparation'],
            ['name' => 'Exam preparation', 'is_active' => true, 'sort_order' => 1]
        );

        return compact('subject', 'category');
    }
}
