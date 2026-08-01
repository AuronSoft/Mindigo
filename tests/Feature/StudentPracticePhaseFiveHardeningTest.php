<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Tests\TestCase;

class StudentPracticePhaseFiveHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_start_resumes_the_same_active_session(): void
    {
        /** @var User $student */
        $student = User::factory()->create(['role' => 'student']);
        Question::factory()->count(3)->create(['subject' => 'Physics', 'practice_status' => Question::PRACTICE_READY]);
        $payload = ['mode' => 'subject', 'subject' => 'Physics', 'question_count' => 3];

        $this->actingAs($student)->post(route('student.practice.start'), $payload)->assertRedirect();
        $first = PracticeAttempt::query()->sole();
        $this->actingAs($student)->post(route('student.practice.start'), $payload)
            ->assertRedirect(route('student.practice.attempt', $first));

        $this->assertSame(1, PracticeAttempt::query()->count());
        $this->assertNotNull($first->request_fingerprint);
        $this->assertNotNull($first->expires_at);
    }

    public function test_duplicate_answer_and_complete_requests_are_idempotent(): void
    {
        [$student, $question, $attempt] = $this->startedAttempt();
        $payload = ['question_id' => $question->id, 'answer' => ['choice' => 'A']];

        $this->actingAs($student)->postJson(route('student.practice.submit-answer', $attempt), $payload)
            ->assertOk()->assertJsonPath('is_correct', true);
        $this->actingAs($student)->postJson(route('student.practice.submit-answer', $attempt), $payload)
            ->assertOk()->assertJsonPath('is_correct', true);
        $this->assertSame(1, $attempt->answers()->sole()->answer_revision);

        $this->actingAs($student)->post(route('student.practice.complete', $attempt))->assertRedirect();
        $completedAt = $attempt->fresh()->completed_at;
        $this->actingAs($student)->post(route('student.practice.complete', $attempt))
            ->assertRedirect(route('student.practice.result', $attempt));
        $this->assertTrue($completedAt->equalTo($attempt->fresh()->completed_at));
    }

    public function test_expired_session_rejects_writes_and_is_reconciled_once(): void
    {
        [$student, $question, $attempt] = $this->startedAttempt();
        $attempt->update(['expires_at' => now()->subMinute()]);

        $this->actingAs($student)->postJson(route('student.practice.submit-answer', $attempt), [
            'question_id' => $question->id, 'answer' => ['choice' => 'A'],
        ])->assertUnprocessable()->assertJsonValidationErrors('attempt');

        $attempt->refresh();
        $this->assertSame(PracticeAttempt::STATUS_EXPIRED, $attempt->status);
        $this->assertNull($attempt->answers()->sole()->student_answer);
        $expiredAt = $attempt->completed_at;

        $this->actingAs($student)->get(route('student.practice.attempt', $attempt))
            ->assertRedirect(route('student.practice.index'));
        $this->assertTrue($expiredAt->equalTo($attempt->fresh()->completed_at));
        $this->actingAs($student)->post(route('student.practice.complete', $attempt))
            ->assertSessionHasErrors('attempt');
    }

    public function test_question_snapshot_keeps_session_stable_after_question_changes(): void
    {
        [$student, $question, $attempt] = $this->startedAttempt('Original immutable question');
        $question->update(['content' => 'Changed question', 'correct_answers' => ['B'], 'status' => 'rejected']);

        $this->actingAs($student)->get(route('student.practice.attempt', $attempt))
            ->assertOk()->assertSee('Original immutable question')->assertDontSee('Changed question');

        $question->delete();

        $this->actingAs($student)->get(route('student.practice.attempt', $attempt))
            ->assertOk()->assertSee('Original immutable question')->assertDontSee('Changed question');
        $this->actingAs($student)->postJson(route('student.practice.submit-answer', $attempt), [
            'question_id' => $question->id, 'answer' => ['choice' => 'A'],
        ])->assertOk()->assertJsonPath('is_correct', true);
        $this->actingAs($student)->post(route('student.practice.complete', $attempt))->assertRedirect();
        $this->actingAs($student)->get(route('student.practice.result', $attempt))
            ->assertOk()->assertSee('Original immutable question')->assertDontSee('Changed question');
    }

    public function test_invalid_answer_shape_and_question_injection_do_not_modify_attempt(): void
    {
        [$student, $question, $attempt] = $this->startedAttempt();
        $outside = Question::factory()->create(['practice_status' => Question::PRACTICE_READY]);

        $this->actingAs($student)->postJson(route('student.practice.submit-answer', $attempt), [
            'question_id' => $question->id, 'answer' => ['choices' => []],
        ])->assertUnprocessable()->assertJsonValidationErrors('answer');
        $this->actingAs($student)->postJson(route('student.practice.submit-answer', $attempt), [
            'question_id' => $outside->id, 'answer' => ['choice' => 'A'],
        ])->assertUnprocessable()->assertJsonValidationErrors('question_id');

        $this->assertNull($attempt->answers()->sole()->student_answer);
        $this->assertSame(0, $attempt->answers()->sole()->answer_revision);
    }

    public function test_direct_url_access_is_private_and_history_is_paginated(): void
    {
        [$student, $question, $attempt] = $this->startedAttempt();
        /** @var User $outsider */
        $outsider = User::factory()->create(['role' => 'student']);

        $this->actingAs($outsider)->get(route('student.practice.attempt', $attempt))->assertForbidden();
        $this->actingAs($outsider)->get(route('student.practice.result', $attempt))->assertForbidden();
        $this->actingAs($outsider)->postJson(route('student.practice.submit-answer', $attempt), [
            'question_id' => $question->id, 'answer' => ['choice' => 'A'],
        ])->assertForbidden();

        PracticeAttempt::query()->whereKey($attempt->id)->update([
            'status' => PracticeAttempt::STATUS_COMPLETED, 'completed_at' => now(), 'score' => 100,
        ]);
        for ($index = 0; $index < 16; $index++) {
            PracticeAttempt::query()->create([
                'student_id' => $student->id, 'mode' => 'mixed', 'total_questions' => 1,
                'correct_answers' => 1, 'score' => 100, 'status' => PracticeAttempt::STATUS_COMPLETED,
                'started_at' => now()->subMinutes(2), 'last_activity_at' => now(), 'completed_at' => now(),
            ]);
        }

        $this->actingAs($student)->get(route('student.practice.history'))
            ->assertOk()->assertViewHas('history', fn ($history): bool => $history->perPage() === 15 && $history->total() === 17);
    }

    public function test_student_workspace_endpoints_reject_guests_and_teachers(): void
    {
        /** @var User $teacher */
        $teacher = User::factory()->create(['role' => 'teacher']);
        $routes = [
            route('student.practice.index'),
            route('student.practice.history'),
            route('student.practice.analytics.index'),
            route('student.practice.adaptive.index'),
            route('student.practice.skills.index'),
        ];

        foreach ($routes as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }

        foreach ($routes as $url) {
            $this->actingAs($teacher)->get($url)->assertRedirect(route('teacher.dashboard'));
        }
    }

    public function test_practice_workspace_exposes_accessible_feedback_and_controls(): void
    {
        [$student, $question, $attempt] = $this->startedAttempt();

        $this->actingAs($student)->get(route('student.practice.index'))
            ->assertOk()
            ->assertSee('role="search"', false)
            ->assertSee('aria-label="'.__('student-practice::app.filter_title').'"', false);
        $this->actingAs($student)->get(route('student.practice.attempt', $attempt))
            ->assertOk()
            ->assertSee('role="status"', false)
            ->assertSee('aria-live="polite"', false)
            ->assertSee((string) $question->content);
    }

    private function startedAttempt(string $content = 'Stable question'): array
    {
        /** @var User $student */
        $student = User::factory()->create(['role' => 'student']);
        $question = Question::factory()->create([
            'subject' => 'Mathematics',
            'content' => $content,
            'type' => 'single_choice',
            'correct_answers' => ['A'],
            'status' => 'approved',
            'practice_status' => Question::PRACTICE_READY,
        ]);
        $this->actingAs($student)->post(route('student.practice.start'), [
            'mode' => 'subject', 'subject' => 'Mathematics', 'question_count' => 1,
        ])->assertRedirect();

        return [$student, $question, PracticeAttempt::query()->sole()];
    }
}
