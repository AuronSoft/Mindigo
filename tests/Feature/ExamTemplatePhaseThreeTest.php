<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\QuestionBank\Models\Question;
use Tests\TestCase;

class ExamTemplatePhaseThreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_open_localized_template_workspace(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);

        $this->actingAs($teacher)->get(route('teacher.exam-templates.index'))
            ->assertOk()
            ->assertSee(__('Mindigo-exam-management::app.template_builder.title'));
        $this->actingAs($teacher)->get(route('teacher.exam-templates.create'))
            ->assertOk()
            ->assertSee(__('Mindigo-exam-management::app.template_builder.save_draft'));
    }

    public function test_teacher_builds_a_draft_from_owned_and_shared_questions(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $other = $this->createUser(['role' => 'teacher']);
        $ownedDraft = Question::factory()->create(['created_by' => $teacher->id, 'status' => 'draft', 'content' => 'Owned draft']);
        $shared = Question::factory()->create(['created_by' => $other->id, 'status' => 'approved', 'content' => 'Shared approved']);

        $response = $this->actingAs($teacher)->post(route('teacher.exam-templates.store'), $this->payload([$ownedDraft->id => 2, $shared->id => 3]));

        $template = ExamTemplate::query()->firstOrFail();
        $response->assertRedirect(route('teacher.exam-templates.edit', $template));
        $this->assertSame('draft', $template->status);
        $this->assertSame(2, $template->total_questions);
        $this->assertSame('5.00', $template->total_points);
        $this->assertSame(['Owned draft', 'Shared approved'], $template->versions()->first()->questions()->pluck('content')->all());
    }

    public function test_question_snapshot_does_not_change_when_source_is_edited(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $question = Question::factory()->create(['created_by' => $teacher->id, 'content' => 'Original question']);
        $this->actingAs($teacher)->post(route('teacher.exam-templates.store'), $this->payload([$question->id => 1]));

        $question->update(['content' => 'Edited source']);

        $this->assertSame('Original question', ExamTemplate::query()->firstOrFail()->versions()->first()->questions()->first()->content);
    }

    public function test_editing_a_ready_template_creates_a_new_version(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $question = Question::factory()->create(['created_by' => $teacher->id]);
        $this->actingAs($teacher)->post(route('teacher.exam-templates.store'), $this->payload([$question->id => 1]));
        $template = ExamTemplate::query()->firstOrFail();

        $this->actingAs($teacher)->post(route('teacher.exam-templates.ready', $template))->assertRedirect();
        $this->actingAs($teacher)->put(route('teacher.exam-templates.update', $template), $this->payload([$question->id => 4], 'Version two'))->assertRedirect();

        $template->refresh();
        $this->assertSame(2, $template->current_version);
        $this->assertSame('draft', $template->status);
        $this->assertNotNull($template->versions()->where('version', 1)->firstOrFail()->locked_at);
        $this->assertSame('1.00', $template->versions()->where('version', 1)->firstOrFail()->total_points);
        $this->assertSame('4.00', $template->versions()->where('version', 2)->firstOrFail()->total_points);
    }

    public function test_admin_student_and_other_teacher_cannot_author_templates(): void
    {
        $owner = $this->createUser(['role' => 'teacher']);
        $question = Question::factory()->create(['created_by' => $owner->id]);
        $this->actingAs($owner)->post(route('teacher.exam-templates.store'), $this->payload([$question->id => 1]));
        $template = ExamTemplate::query()->firstOrFail();

        foreach (['admin', 'student'] as $role) {
            $user = $this->createUser(['role' => $role]);
            $this->actingAs($user)->get(route('teacher.exam-templates.index'))->assertRedirect();
        }

        $otherTeacher = $this->createUser(['role' => 'teacher']);
        $this->actingAs($otherTeacher)->get(route('teacher.exam-templates.edit', $template))->assertForbidden();
    }

    public function test_teacher_cannot_use_another_teachers_unshared_draft_question(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $other = $this->createUser(['role' => 'teacher']);
        $privateQuestion = Question::factory()->create(['created_by' => $other->id, 'status' => 'draft']);

        $this->actingAs($teacher)
            ->post(route('teacher.exam-templates.store'), $this->payload([$privateQuestion->id => 1]))
            ->assertSessionHasErrors('sections');

        $this->assertDatabaseCount('exam_templates', 0);
    }

    private function payload(array $questions, string $title = 'Final course exam'): array
    {
        return [
            'title' => $title,
            'subject' => 'English',
            'instructions' => 'Complete every question.',
            'settings' => ['shuffle_questions' => true],
            'sections' => [[
                'title' => 'Core knowledge',
                'questions' => collect($questions)->map(fn ($points, $id) => ['id' => $id, 'points' => $points])->values()->all(),
            ]],
        ];
    }
}
