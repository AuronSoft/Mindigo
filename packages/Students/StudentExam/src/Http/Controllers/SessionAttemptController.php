<?php

namespace Mindigo\StudentExam\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Services\ExamCandidateAttemptService;
use Mindigo\StudentExam\Http\Requests\AutosaveSessionAnswerRequest;
use Mindigo\StudentExam\Http\Requests\SessionSecurityEventRequest;
use Mindigo\StudentExam\Http\Requests\SubmitSessionAttemptRequest;

class SessionAttemptController extends Controller
{
    public function __construct(private readonly ExamCandidateAttemptService $attempts) {}

    public function index(Request $request): View
    {
        return view('student-exam::sessions.index', $this->attempts->workspace($request->user()));
    }

    public function start(Request $request, ExamSession $session): RedirectResponse
    {
        $attempt = $this->attempts->start($session, $request->user());

        return redirect()->route('student.exam-sessions.take', $attempt);
    }

    public function take(Request $request, ExamSessionAttempt $attempt): View
    {
        $questions = $this->attempts->questions($attempt, $request->user());
        abort_unless($attempt->isActive(), 409, __('Mindigo-exam-management::app.candidate_attempt.not_active'));
        $attempt->load(['session.version.template', 'answers']);

        return view('student-exam::sessions.take', [
            'attempt' => $attempt,
            'questions' => $questions,
            'savedAnswers' => $attempt->answers->keyBy('exam_template_question_id'),
        ]);
    }

    public function autosave(AutosaveSessionAnswerRequest $request, ExamSessionAttempt $attempt): JsonResponse
    {
        $saved = $this->attempts->saveAnswer($attempt, $request->user(), $request->integer('question_id'), $request->validated('answer'));

        return response()->json(['ok' => $saved], $saved ? 200 : 409);
    }

    public function heartbeat(Request $request, ExamSessionAttempt $attempt): JsonResponse
    {
        $active = $this->attempts->heartbeat($attempt, $request->user());

        return response()->json(['ok' => $active], $active ? 200 : 409);
    }

    public function securityEvent(SessionSecurityEventRequest $request, ExamSessionAttempt $attempt): JsonResponse
    {
        $recorded = $this->attempts->recordSecurityEvent($attempt, $request->user(), (string) $request->string('type'));

        return response()->json(['ok' => $recorded], $recorded ? 200 : 409);
    }

    public function submit(SubmitSessionAttemptRequest $request, ExamSessionAttempt $attempt): RedirectResponse
    {
        $this->attempts->submit($attempt, $request->user(), $request->validated('answers', []));

        return redirect()->route('student.exam-sessions.result', $attempt);
    }

    public function result(Request $request, ExamSessionAttempt $attempt): View
    {
        abort_unless((int) $attempt->user_id === (int) $request->user()->getAuthIdentifier(), 403);
        abort_if($attempt->status === ExamSessionAttempt::STATUS_IN_PROGRESS, 409);
        $attempt->load('session.version.template');
        $policy = $attempt->session->result_policy;
        $visible = $policy === 'immediately'
            || ($policy === 'after_end' && $attempt->session->ends_at?->isPast())
            || ($policy === 'after_release' && $attempt->released_at !== null);

        return view('student-exam::sessions.result', compact('attempt', 'visible'));
    }
}
