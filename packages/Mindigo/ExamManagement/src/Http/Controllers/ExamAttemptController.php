<?php

namespace Mindigo\ExamManagement\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Http\Requests\ExamAttemptAnswerRequest;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\ExamManagement\Services\ExamAttemptService;
use Symfony\Component\HttpFoundation\Response;

class ExamAttemptController extends Controller
{
    public function __construct(private ExamAttemptService $attempts) {}

    public function start(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'exams.attempt');

        return redirect()->route('exams.attempts.take', $this->attempts->start($exam, $request->user()));
    }

    public function take(Request $request, ExamAttempt $attempt)
    {
        $this->authorizeAttempt($request, $attempt);

        if ($attempt->status !== 'in_progress') {
            return redirect()->route('exams.attempts.result', $attempt);
        }

        if ($attempt->expires_at && $attempt->expires_at->isPast()) {
            $this->attempts->finalize($attempt, $attempt->autosave_payload['answers'] ?? [], 'expired');

            return redirect()->route('exams.attempts.result', $attempt);
        }

        $attempt->load('exam');

        return view('Mindigo-exam-management::take', [
            'attempt' => $attempt,
            'exam' => $attempt->exam,
            'questions' => $this->attempts->orderedQuestions($attempt),
            'savedAnswers' => $attempt->autosave_payload['answers'] ?? [],
        ]);
    }

    public function autosave(ExamAttemptAnswerRequest $request, ExamAttempt $attempt): JsonResponse
    {
        if ($attempt->status !== 'in_progress') {
            return response()->json(['ok' => false], Response::HTTP_CONFLICT);
        }

        if (! $this->attempts->autosave($attempt, $request->answers())) {
            return response()->json(['ok' => false], Response::HTTP_CONFLICT);
        }

        return response()->json(['ok' => true, 'saved_at' => now()->toIso8601String()]);
    }

    public function submit(ExamAttemptAnswerRequest $request, ExamAttempt $attempt): RedirectResponse
    {
        if ($attempt->status === 'in_progress') {
            $this->attempts->finalize($attempt, $request->answers(), 'submitted');
        }

        return redirect()->route('exams.attempts.result', $attempt);
    }

    public function logViolation(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $this->authorizeAttempt($request, $attempt);

        return response()->json([
            'ok' => true,
            'count' => $this->attempts->logViolation($attempt),
        ]);
    }

    public function result(Request $request, ExamAttempt $attempt)
    {
        $this->authorizeAttempt($request, $attempt, true);
        $attempt->load(['exam', 'answers.question']);

        return view('Mindigo-exam-management::result', [
            'attempt' => $attempt,
            'exam' => $attempt->exam,
        ]);
    }

    private function authorizeAttempt(Request $request, ExamAttempt $attempt, bool $allowStaff = false): void
    {
        if ($this->attempts->canViewAttempt($request->user(), $attempt, $allowStaff)) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN);
    }

    private function authorizePermission(User $user, string $permission): void
    {
        if (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($permission)) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN);
    }
}
