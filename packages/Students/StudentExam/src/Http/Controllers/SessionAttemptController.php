<?php

namespace Mindigo\StudentExam\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Services\ExamCandidateAttemptService;

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
        abort_unless($attempt->isActive(), 409, __('Mindigo-exam-management::app.candidate_attempt.not_active'));
        $attempt->load('session.version.template');

        return view('student-exam::sessions.take', [
            'attempt' => $attempt,
            'questions' => $this->attempts->questions($attempt, $request->user()),
        ]);
    }
}
