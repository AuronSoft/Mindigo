<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mindigo\AuditLog\Models\AuditLog;
use Mindigo\Notification\Notifications\TeacherApplicationProvisioningNotification;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherCourse\Models\CourseCategory;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;
use Mindigo\TeacherOnboarding\Models\TeacherApplicationInterview;
use Mindigo\Auth\Models\User;
use Tests\TestCase;

class TeacherApplicationProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_passed_application_and_provision_teacher_profile(): void
    {
        Notification::fake();
        $admin = $this->createUser(['role' => 'admin']);
        $applicant = $this->createUser(['role' => 'student']);
        [$application] = $this->passedApplication($applicant);

        $this->actingAs($admin)
            ->patch(route('admin.teacher-applications.provisioning.update', $application), [
                'action' => 'approve',
                'note' => 'Passed interview and approved for teaching workspace.',
            ])
            ->assertRedirect(route('admin.teacher-applications.show', $application));

        $this->assertDatabaseHas('users', [
            'id' => $applicant->id,
            'role' => 'teacher',
        ]);

        $this->assertDatabaseHas('teacher_profiles', [
            'user_id' => $applicant->id,
            'headline' => $application->specialization,
            'specialization' => $application->specialization,
            'experience_years' => $application->experience_years,
            'is_public' => false,
        ]);

        $this->assertDatabaseHas('teacher_applications', [
            'id' => $application->id,
            'status' => TeacherApplication::STATUS_APPROVED,
            'teacher_provision_status' => TeacherApplication::PROVISION_ACTIVE,
            'provisioned_user_role' => 'student',
            'provisioned_by' => $admin->id,
            'provisioning_note' => 'Passed interview and approved for teaching workspace.',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'teacher_application_approve',
            'module' => 'teacher-onboarding',
            'auditable_type' => TeacherApplication::class,
            'auditable_id' => $application->id,
            'user_id' => $admin->id,
        ]);

        Notification::assertSentTo($applicant, TeacherApplicationProvisioningNotification::class);
    }

    public function test_application_without_passed_interview_cannot_be_approved(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $applicant = $this->createUser(['role' => 'student']);
        $application = $this->application([
            'user_id' => $applicant->id,
            'status' => TeacherApplication::STATUS_INTERVIEWED,
        ]);

        TeacherApplicationInterview::query()->create([
            'teacher_application_id' => $application->id,
            'interviewer_id' => $admin->id,
            'scheduled_at' => now()->subDay(),
            'mode' => TeacherApplicationInterview::MODE_ONLINE,
            'meeting_url' => 'https://meet.google.com/abc-defg-hij',
            'result' => TeacherApplicationInterview::RESULT_FAILED,
            'evaluated_at' => now(),
            'evaluated_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.teacher-applications.provisioning.update', $application), [
                'action' => 'approve',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $applicant->id,
            'role' => 'student',
        ]);
    }

    public function test_admin_can_suspend_and_revoke_teacher_access_without_deleting_history(): void
    {
        Notification::fake();
        $admin = $this->createUser(['role' => 'admin']);
        $applicant = $this->createUser(['role' => 'teacher']);
        [$application] = $this->passedApplication($applicant, [
            'status' => TeacherApplication::STATUS_APPROVED,
            'teacher_provision_status' => TeacherApplication::PROVISION_ACTIVE,
            'provisioned_user_role' => 'student',
            'provisioned_by' => $admin->id,
            'provisioned_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.teacher-applications.provisioning.update', $application), [
                'action' => 'suspend',
                'note' => 'Temporary compliance review.',
            ])
            ->assertRedirect(route('admin.teacher-applications.show', $application));

        $this->assertDatabaseHas('users', [
            'id' => $applicant->id,
            'role' => 'student',
        ]);

        $this->assertDatabaseHas('teacher_applications', [
            'id' => $application->id,
            'status' => TeacherApplication::STATUS_SUSPENDED,
            'teacher_provision_status' => TeacherApplication::PROVISION_SUSPENDED,
            'provisioning_note' => 'Temporary compliance review.',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.teacher-applications.provisioning.update', $application->refresh()), [
                'action' => 'revoke',
                'note' => 'Verification failed after review.',
            ])
            ->assertRedirect(route('admin.teacher-applications.show', $application));

        $this->assertDatabaseHas('teacher_applications', [
            'id' => $application->id,
            'status' => TeacherApplication::STATUS_REVOKED,
            'teacher_provision_status' => TeacherApplication::PROVISION_REVOKED,
            'provisioning_note' => 'Verification failed after review.',
        ]);

        $this->assertDatabaseHas('teacher_application_interviews', [
            'teacher_application_id' => $application->id,
            'result' => TeacherApplicationInterview::RESULT_PASSED,
        ]);

        $this->assertSame(2, AuditLog::query()->where('module', 'teacher-onboarding')->count());
        Notification::assertSentTo($applicant, TeacherApplicationProvisioningNotification::class, 2);
    }

    public function test_suspend_and_revoke_require_note_and_valid_state(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $applicant = $this->createUser(['role' => 'student']);
        [$application] = $this->passedApplication($applicant);

        $this->actingAs($admin)
            ->patch(route('admin.teacher-applications.provisioning.update', $application), [
                'action' => 'suspend',
            ])
            ->assertSessionHasErrors('note');

        $this->actingAs($admin)
            ->patch(route('admin.teacher-applications.provisioning.update', $application), [
                'action' => 'revoke',
                'note' => 'Cannot revoke before approval.',
            ])
            ->assertForbidden();
    }

    public function test_non_admin_cannot_provision_teacher_access(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $applicant = $this->createUser(['role' => 'student']);
        [$application] = $this->passedApplication($applicant);

        $this->actingAs($student)
            ->patch(route('admin.teacher-applications.provisioning.update', $application), [
                'action' => 'approve',
            ])
            ->assertForbidden();
    }

    private function passedApplication(User $applicant, array $overrides = []): array
    {
        $admin = $this->createUser(['role' => 'admin']);
        $application = $this->application(array_merge([
            'user_id' => $applicant->id,
            'status' => TeacherApplication::STATUS_INTERVIEWED,
        ], $overrides));

        $interview = TeacherApplicationInterview::query()->create([
            'teacher_application_id' => $application->id,
            'interviewer_id' => $admin->id,
            'scheduled_at' => now()->subDay(),
            'mode' => TeacherApplicationInterview::MODE_ONLINE,
            'meeting_url' => 'https://zoom.us/j/123456789',
            'subject_knowledge_score' => 9,
            'pedagogy_score' => 8,
            'communication_score' => 8,
            'lms_technology_score' => 8,
            'overall_comment' => 'Passed interview.',
            'result' => TeacherApplicationInterview::RESULT_PASSED,
            'evaluated_at' => now(),
            'evaluated_by' => $admin->id,
        ]);

        return [$application, $interview];
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
            'achievements' => 'Excellent teaching award',
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
