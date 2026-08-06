<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mindigo\Notification\Notifications\TeacherApplicationInterviewNotification;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherCourse\Models\CourseCategory;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;
use Mindigo\TeacherOnboarding\Models\TeacherApplicationInterview;
use Tests\TestCase;

class TeacherApplicationInterviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_schedule_interview_for_screening_application_and_applicant_is_notified(): void
    {
        Notification::fake();
        $admin = $this->createUser(['role' => 'admin']);
        $applicant = $this->createUser(['role' => 'student']);
        $application = $this->application([
            'user_id' => $applicant->id,
            'status' => TeacherApplication::STATUS_SCREENING,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.teacher-applications.interviews.store', $application), $this->schedulePayload())
            ->assertRedirect();

        $this->assertDatabaseHas('teacher_application_interviews', [
            'teacher_application_id' => $application->id,
            'interviewer_id' => $admin->id,
            'mode' => TeacherApplicationInterview::MODE_ONLINE,
            'meeting_url' => 'https://meet.google.com/abc-defg-hij',
        ]);

        $this->assertDatabaseHas('teacher_applications', [
            'id' => $application->id,
            'status' => TeacherApplication::STATUS_INTERVIEW_SCHEDULED,
            'reviewed_by' => $admin->id,
        ]);

        Notification::assertSentTo($applicant, TeacherApplicationInterviewNotification::class);
    }

    public function test_interview_cannot_be_scheduled_outside_screening(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $application = $this->application([
            'status' => TeacherApplication::STATUS_SUBMITTED,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.teacher-applications.interviews.store', $application), $this->schedulePayload())
            ->assertForbidden();

        $this->assertDatabaseMissing('teacher_application_interviews', [
            'teacher_application_id' => $application->id,
        ]);
    }

    public function test_admin_can_update_schedule_and_open_interview_detail(): void
    {
        Notification::fake();
        $admin = $this->createUser(['role' => 'admin']);
        $applicant = $this->createUser(['role' => 'student']);
        [$application, $interview] = $this->interviewFixture($admin, $applicant);

        $this->actingAs($admin)
            ->patch(route('admin.teacher-applications.interviews.update', [$application, $interview]), [
                'scheduled_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'mode' => TeacherApplicationInterview::MODE_OFFLINE,
                'meeting_url' => null,
                'pre_interview_note' => 'Bring portfolio and ID.',
            ])
            ->assertRedirect(route('admin.teacher-applications.interviews.show', [$application, $interview]));

        $this->assertDatabaseHas('teacher_application_interviews', [
            'id' => $interview->id,
            'mode' => TeacherApplicationInterview::MODE_OFFLINE,
            'meeting_url' => null,
            'pre_interview_note' => 'Bring portfolio and ID.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.teacher-applications.interviews.show', [$application, $interview]))
            ->assertOk()
            ->assertSee($application->full_name)
            ->assertSee(__('teacher-onboarding::interview.evaluation'));

        Notification::assertSentTo($applicant, TeacherApplicationInterviewNotification::class);
    }

    public function test_admin_can_evaluate_interview_and_notify_applicant(): void
    {
        Notification::fake();
        $admin = $this->createUser(['role' => 'admin']);
        $applicant = $this->createUser(['role' => 'student']);
        [$application, $interview] = $this->interviewFixture($admin, $applicant);

        $this->actingAs($admin)
            ->patch(route('admin.teacher-applications.interviews.evaluate', [$application, $interview]), [
                'subject_knowledge_score' => 9,
                'pedagogy_score' => 8,
                'communication_score' => 8,
                'lms_technology_score' => 7,
                'overall_comment' => 'Strong classroom communication and solid LMS readiness.',
                'result' => TeacherApplicationInterview::RESULT_PASSED,
            ])
            ->assertRedirect(route('admin.teacher-applications.interviews.show', [$application, $interview]));

        $this->assertDatabaseHas('teacher_application_interviews', [
            'id' => $interview->id,
            'subject_knowledge_score' => 9,
            'pedagogy_score' => 8,
            'communication_score' => 8,
            'lms_technology_score' => 7,
            'result' => TeacherApplicationInterview::RESULT_PASSED,
            'evaluated_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('teacher_applications', [
            'id' => $application->id,
            'status' => TeacherApplication::STATUS_INTERVIEWED,
            'status_note' => 'Strong classroom communication and solid LMS readiness.',
        ]);

        Notification::assertSentTo($applicant, TeacherApplicationInterviewNotification::class);
    }

    public function test_second_interview_result_returns_application_to_screening(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $applicant = $this->createUser(['role' => 'student']);
        [$application, $interview] = $this->interviewFixture($admin, $applicant);

        $this->actingAs($admin)
            ->patch(route('admin.teacher-applications.interviews.evaluate', [$application, $interview]), [
                'subject_knowledge_score' => 6,
                'pedagogy_score' => 6,
                'communication_score' => 7,
                'lms_technology_score' => 6,
                'overall_comment' => 'Needs one more teaching simulation before decision.',
                'result' => TeacherApplicationInterview::RESULT_NEED_SECOND_INTERVIEW,
            ])
            ->assertRedirect(route('admin.teacher-applications.interviews.show', [$application, $interview]));

        $this->assertDatabaseHas('teacher_applications', [
            'id' => $application->id,
            'status' => TeacherApplication::STATUS_SCREENING,
        ]);
    }

    public function test_non_admin_cannot_access_interview_routes(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $student = $this->createUser(['role' => 'student']);
        $applicant = $this->createUser(['role' => 'student']);
        [$application, $interview] = $this->interviewFixture($admin, $applicant);

        $this->actingAs($student)
            ->get(route('admin.teacher-applications.interviews.show', [$application, $interview]))
            ->assertForbidden();

        $this->actingAs($student)
            ->patch(route('admin.teacher-applications.interviews.evaluate', [$application, $interview]), [
                'subject_knowledge_score' => 10,
                'pedagogy_score' => 10,
                'communication_score' => 10,
                'lms_technology_score' => 10,
                'overall_comment' => 'Not allowed.',
                'result' => TeacherApplicationInterview::RESULT_PASSED,
            ])
            ->assertForbidden();
    }

    private function interviewFixture($admin, $applicant): array
    {
        $application = $this->application([
            'user_id' => $applicant->id,
            'status' => TeacherApplication::STATUS_INTERVIEW_SCHEDULED,
        ]);

        $interview = TeacherApplicationInterview::query()->create([
            'teacher_application_id' => $application->id,
            'interviewer_id' => $admin->id,
            'scheduled_at' => now()->addDay(),
            'mode' => TeacherApplicationInterview::MODE_ONLINE,
            'meeting_url' => 'https://zoom.us/j/123456789',
            'pre_interview_note' => 'Prepare teaching demo.',
        ]);

        return [$application, $interview];
    }

    private function schedulePayload(array $overrides = []): array
    {
        return array_merge([
            'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'mode' => TeacherApplicationInterview::MODE_ONLINE,
            'meeting_url' => 'https://meet.google.com/abc-defg-hij',
            'pre_interview_note' => 'Prepare a 15 minute demo lesson.',
        ], $overrides);
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
