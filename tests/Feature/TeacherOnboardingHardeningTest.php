<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherCourse\Models\CourseCategory;
use Mindigo\TeacherCourse\Models\TeacherProfile;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;
use Mindigo\TeacherOnboarding\Models\TeacherApplicationInterview;
use Tests\TestCase;

class TeacherOnboardingHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_submission_is_audited(): void
    {
        $this->seedMasterData();

        $this->post(route('teacher-applications.store'), [
            'full_name' => 'Nguyen Candidate',
            'email' => 'candidate@mindigo.test',
            'phone' => '0909123456',
            'application_type' => 'teacher',
            'subject_id' => Subject::query()->value('id'),
            'category_id' => CourseCategory::query()->value('id'),
            'education_level' => 'upper_secondary',
            'specialization' => 'Mathematics',
            'teaching_mode' => 'online',
            'experience_years' => 3,
            'teaching_method' => 'Structured lessons and feedback.',
            'terms_accepted' => '1',
        ])->assertRedirect(route('teacher-applications.create'));

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'teacher-onboarding',
            'action' => 'teacher_application_submitted',
        ]);
    }

    public function test_private_documents_require_signed_admin_url(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('teacher-applications/private/id.pdf', 'identity');
        $admin = $this->createUser(['role' => 'admin']);
        $application = $this->application([
            'verification_documents' => [
                'identity' => ['disk' => 'local', 'path' => 'teacher-applications/private/id.pdf', 'name' => 'id.pdf'],
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.teacher-applications.documents.show', [$application, 'identity']))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(URL::temporarySignedRoute('admin.teacher-applications.documents.show', now()->addMinutes(5), [$application, 'identity']))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_nested_interview_idor_is_blocked(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $firstApplication = $this->application(['status' => TeacherApplication::STATUS_INTERVIEW_SCHEDULED]);
        $secondApplication = $this->application([
            'application_code' => 'TA-260806-IDOR02',
            'email' => 'second@mindigo.test',
            'status' => TeacherApplication::STATUS_INTERVIEW_SCHEDULED,
        ]);
        $foreignInterview = $this->interview($secondApplication, $admin->id);

        $this->actingAs($admin)
            ->patch(route('admin.teacher-applications.interviews.update', [$firstApplication, $foreignInterview]), $this->schedulePayload())
            ->assertForbidden();
    }

    public function test_onboarding_review_interview_and_profile_actions_are_audited(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $applicant = $this->createUser(['role' => 'student']);
        $application = $this->application(['user_id' => $applicant->id]);

        $this->actingAs($admin)
            ->patch(route('admin.teacher-applications.update', $application), [
                'status' => TeacherApplication::STATUS_SCREENING,
                'internal_note' => 'Ready for interview.',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.teacher-applications.interviews.store', $application->refresh()), $this->schedulePayload())
            ->assertRedirect();

        $interview = TeacherApplicationInterview::query()->where('teacher_application_id', $application->id)->firstOrFail();
        $this->actingAs($admin)
            ->patch(route('admin.teacher-applications.interviews.evaluate', [$application->refresh(), $interview]), [
                'subject_knowledge_score' => 9,
                'pedagogy_score' => 8,
                'communication_score' => 8,
                'lms_technology_score' => 8,
                'overall_comment' => 'Ready to teach on Mindigo.',
                'result' => TeacherApplicationInterview::RESULT_PASSED,
            ])
            ->assertRedirect();

        $teacher = $this->createUser(['role' => 'teacher']);
        $profile = TeacherProfile::query()->create(['user_id' => $teacher->id]);
        $this->actingAs($teacher)
            ->put(route('teacher.profile.update', $profile), [
                'headline' => 'Verified mentor',
                'biography' => 'Teaching profile',
                'specialization' => 'Programming',
                'experience_years' => 5,
                'qualifications' => 'Verified degree',
                'is_public' => true,
            ])
            ->assertRedirect();

        foreach ([
            'teacher_application_reviewed',
            'teacher_application_interview_scheduled',
            'teacher_application_interview_evaluated',
            'teacher_public_profile_updated',
        ] as $action) {
            $this->assertDatabaseHas('audit_logs', ['module' => 'teacher-onboarding', 'action' => $action]);
        }
    }

    private function application(array $overrides = []): TeacherApplication
    {
        $masterData = $this->seedMasterData();

        return TeacherApplication::query()->create([
            'application_code' => 'TA-260806-'.str()->upper(str()->random(6)),
            'status' => TeacherApplication::STATUS_SUBMITTED,
            'application_type' => 'teacher',
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
            ...$overrides,
        ]);
    }

    private function interview(TeacherApplication $application, int $adminId): TeacherApplicationInterview
    {
        return TeacherApplicationInterview::query()->create([
            'teacher_application_id' => $application->id,
            'interviewer_id' => $adminId,
            'scheduled_at' => now()->addDay(),
            'mode' => TeacherApplicationInterview::MODE_ONLINE,
            'meeting_url' => 'https://meet.google.com/abc-defg-hij',
        ]);
    }

    private function schedulePayload(): array
    {
        return [
            'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'mode' => TeacherApplicationInterview::MODE_ONLINE,
            'meeting_url' => 'https://meet.google.com/abc-defg-hij',
            'pre_interview_note' => 'Prepare a teaching demo.',
        ];
    }

    private function seedMasterData(): array
    {
        return [
            'subject' => Subject::query()->firstOrCreate(
                ['slug' => 'mathematics'],
                ['name' => 'Mathematics', 'code' => 'MATH', 'status' => 'active', 'sort_order' => 1],
            ),
            'category' => CourseCategory::query()->firstOrCreate(
                ['slug' => 'exam-preparation'],
                ['name' => 'Exam preparation', 'is_active' => true, 'sort_order' => 1],
            ),
        ];
    }
}
