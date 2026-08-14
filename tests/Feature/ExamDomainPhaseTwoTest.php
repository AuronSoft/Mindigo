<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Mindigo\ExamManagement\Models\ExamCandidate;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Models\ExamTemplateQuestion;
use Mindigo\ExamManagement\Models\ExamTemplateVersion;
use Tests\TestCase;

class ExamDomainPhaseTwoTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_domain_schema_is_available_without_changing_existing_exam_tables(): void
    {
        foreach (['exams', 'exam_attempts', 'exam_templates', 'exam_template_versions', 'exam_sections', 'exam_template_questions', 'exam_sessions', 'exam_assignments', 'exam_candidates'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table {$table}");
        }
    }

    public function test_template_version_snapshots_feed_independent_exam_sessions(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $template = ExamTemplate::query()->create(['owner_id' => $teacher->id, 'title' => 'PHP cuối khóa', 'slug' => 'php-cuoi-khoa', 'status' => 'ready', 'current_version' => 1]);
        $version = ExamTemplateVersion::query()->create(['exam_template_id' => $template->id, 'created_by' => $teacher->id, 'version' => 1, 'title' => $template->title, 'settings' => ['shuffle_questions' => true]]);
        ExamTemplateQuestion::query()->create(['exam_template_version_id' => $version->id, 'sort_order' => 1, 'type' => 'single_choice', 'content' => 'PHP là gì?', 'options' => ['a' => 'Ngôn ngữ lập trình'], 'correct_answers' => ['a'], 'points' => 2]);
        $session = ExamSession::query()->create(['exam_template_version_id' => $version->id, 'organizer_id' => $teacher->id, 'title' => 'Thi cuối khóa K24', 'slug' => 'thi-cuoi-khoa-k24', 'status' => 'scheduled', 'duration_minutes' => 60]);

        $this->assertSame(1, $template->versions()->count());
        $this->assertSame(1, $version->questions()->count());
        $this->assertTrue($version->isLocked());
        $this->assertTrue($session->isMutable());
    }

    public function test_candidate_keeps_identity_snapshot_when_linked_to_a_student(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student', 'name' => 'Học sinh A']);
        $template = ExamTemplate::query()->create(['owner_id' => $teacher->id, 'title' => 'Toán', 'slug' => 'toan', 'status' => 'ready']);
        $version = ExamTemplateVersion::query()->create(['exam_template_id' => $template->id, 'created_by' => $teacher->id, 'version' => 1, 'title' => 'Toán']);
        $session = ExamSession::query()->create(['exam_template_version_id' => $version->id, 'organizer_id' => $teacher->id, 'title' => 'Thi Toán', 'slug' => 'thi-toan', 'duration_minutes' => 45]);
        $candidate = ExamCandidate::query()->create(['exam_session_id' => $session->id, 'user_id' => $student->id, 'name' => $student->name, 'email' => $student->email, 'student_code' => 'ST001']);

        $this->assertSame('Học sinh A', $candidate->name);
        $this->assertTrue($candidate->user->is($student));
    }

    public function test_only_the_teaching_owner_can_manage_exam_domain_objects(): void
    {
        $owner = $this->createUser(['role' => 'teacher']);
        $other = $this->createUser(['role' => 'teacher']);
        $admin = $this->createUser(['role' => 'admin']);
        $template = ExamTemplate::query()->create(['owner_id' => $owner->id, 'title' => 'Owned', 'slug' => 'owned']);

        $this->assertTrue(Gate::forUser($owner)->allows('update', $template));
        $this->assertFalse(Gate::forUser($other)->allows('update', $template));
        $this->assertFalse(Gate::forUser($admin)->allows('update', $template));
    }
}
