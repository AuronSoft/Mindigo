<?php

namespace Mindigo\ExamManagement\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;
use Mindigo\ExamManagement\Models\ExamQuestion;
use Symfony\Component\HttpFoundation\Response;

class ExamAttemptController extends Controller
{
    public function start(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'exams.attempt');

        if (!$exam->isOpen()) {
            return back()->with('error', __('Mindigo-exam-management::app.messages.exam_not_open'));
        }

        $attemptCount = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->whereIn('status', ['submitted', 'expired'])
            ->count();

        if ($attemptCount >= $exam->max_attempts) {
            return back()->with('error', __('Mindigo-exam-management::app.messages.max_attempts_reached'));
        }

        $attempt = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->where('status', 'in_progress')
            ->first();

        if (!$attempt) {
            $questionIds = $exam->questions()->pluck('id')->all();
            if ($exam->shuffle_questions) {
                shuffle($questionIds);
            }

            $attempt = ExamAttempt::query()->create([
                'exam_id' => $exam->id,
                'user_id' => $request->user()->getAuthIdentifier(),
                'status' => 'in_progress',
                'started_at' => now(),
                'expires_at' => now()->addMinutes($exam->duration_minutes),
                'max_score' => $exam->total_points,
                'question_order' => $questionIds,
            ]);

            $this->audit('start', [], ['attempt_id' => $attempt->id], $attempt);
        }

        return redirect()->route('exams.attempts.take', $attempt);
    }

    public function take(Request $request, ExamAttempt $attempt)
    {
        $this->authorizeAttemptOwner($request, $attempt);

        if ($attempt->status !== 'in_progress') {
            return redirect()->route('exams.attempts.result', $attempt);
        }

        if ($attempt->expires_at && $attempt->expires_at->isPast()) {
            $this->finalizeAttempt($attempt, $attempt->autosave_payload ?? [], 'expired');

            return redirect()->route('exams.attempts.result', $attempt);
        }

        $attempt->load('exam');
        $questions = ExamQuestion::query()
            ->whereIn('id', $attempt->question_order ?? [])
            ->get()
            ->keyBy('id');

        $orderedQuestions = collect($attempt->question_order ?? [])
            ->map(fn ($id) => $questions->get($id))
            ->filter()
            ->values();

        return view('Mindigo-exam-management::take', [
            'attempt' => $attempt,
            'exam' => $attempt->exam,
            'questions' => $orderedQuestions,
            'savedAnswers' => $attempt->autosave_payload['answers'] ?? [],
        ]);
    }

    public function autosave(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $this->authorizeAttemptOwner($request, $attempt);

        if ($attempt->status !== 'in_progress') {
            return response()->json(['ok' => false], Response::HTTP_CONFLICT);
        }

        $attempt->forceFill([
            'autosave_payload' => ['answers' => $request->input('answers', [])],
        ])->save();

        return response()->json(['ok' => true, 'saved_at' => now()->toIso8601String()]);
    }

    public function submit(Request $request, ExamAttempt $attempt): RedirectResponse
    {
        $this->authorizeAttemptOwner($request, $attempt);

        if ($attempt->status === 'in_progress') {
            $this->finalizeAttempt($attempt, ['answers' => $request->input('answers', [])], 'submitted');
        }

        return redirect()->route('exams.attempts.result', $attempt);
    }

    public function logViolation(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $this->authorizeAttemptOwner($request, $attempt);

        if ($attempt->status === 'in_progress') {
            $attempt->increment('tab_leave_count');
        }

        return response()->json(['ok' => true, 'count' => $attempt->fresh()->tab_leave_count]);
    }

    public function result(Request $request, ExamAttempt $attempt)
    {
        $this->authorizeAttemptOwner($request, $attempt, true);
        $attempt->load(['exam', 'answers.question']);

        return view('Mindigo-exam-management::result', [
            'attempt' => $attempt,
            'exam' => $attempt->exam,
        ]);
    }

    private function finalizeAttempt(ExamAttempt $attempt, array $payload, string $status): void
    {
        DB::transaction(function () use ($attempt, $payload, $status) {
            $attempt->load('exam.questions');
            $answers = $payload['answers'] ?? [];
            $score = 0.0;

            foreach ($attempt->exam->questions as $question) {
                $answer = $this->normalizeAnswer($answers[$question->id] ?? null);
                [$isCorrect, $points, $needsReview] = $this->scoreAnswer($question, $answer);
                $score += $points;

                ExamAttemptAnswer::query()->updateOrCreate(
                    [
                        'exam_attempt_id' => $attempt->id,
                        'exam_question_id' => $question->id,
                    ],
                    [
                        'type' => $question->type,
                        'answer' => $answer,
                        'is_correct' => $isCorrect,
                        'points_awarded' => $points,
                        'needs_review' => $needsReview,
                    ]
                );
            }

            $maxScore = (float) $attempt->exam->total_points;
            $percentage = $maxScore > 0 ? round(($score / $maxScore) * 100, 2) : 0;

            $attempt->forceFill([
                'status' => $status,
                'submitted_at' => now(),
                'score' => $score,
                'max_score' => $maxScore,
                'percentage' => $percentage,
                'passed' => $score >= (float) $attempt->exam->passing_score,
                'autosave_payload' => ['answers' => $answers],
            ])->save();
        });

        $this->audit($status, [], ['attempt_id' => $attempt->id, 'score' => $attempt->fresh()->score], $attempt);
    }

    private function scoreAnswer(ExamQuestion $question, array $answer): array
    {
        if ($question->type === 'essay') {
            return [null, 0.0, true];
        }

        $expected = $this->normalizeComparable($question->correct_answers ?? []);
        $actual = $this->normalizeComparable($answer);
        $isCorrect = $expected === $actual && !empty($expected);

        return [$isCorrect, $isCorrect ? (float) $question->points : 0.0, false];
    }

    private function normalizeAnswer(mixed $answer): array
    {
        return collect((array) $answer)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->values()
            ->all();
    }

    private function normalizeComparable(array $answer): array
    {
        return collect($answer)
            ->map(fn ($value) => mb_strtolower(trim((string) $value)))
            ->filter()
            ->sort()
            ->values()
            ->all();
    }

    private function authorizeAttemptOwner(Request $request, ExamAttempt $attempt, bool $allowAdmin = false): void
    {
        $user = $request->user();

        if ($allowAdmin && (int) $attempt->user_id !== (int) $user->getAuthIdentifier() && $user->hasPermissionTo('exams.view') && !$user->isStudent()) {
            return;
        }

        if ((int) $attempt->user_id !== (int) $user->getAuthIdentifier()) {
            abort(Response::HTTP_FORBIDDEN);
        }
    }

    private function authorizePermission(User $user, string $permission): void
    {
        if (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($permission)) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN);
    }

    private function audit(string $action, array $oldValues, array $newValues, ExamAttempt $attempt): void
    {
        if (!class_exists(\Mindigo\AuditLog\Services\AuditLogService::class)) {
            return;
        }

        app(\Mindigo\AuditLog\Services\AuditLogService::class)->record(
            $action,
            'exam_attempts',
            $oldValues,
            $newValues,
            ['attempt_id' => $attempt->id, 'exam_id' => $attempt->exam_id],
            $attempt
        );
    }
}
