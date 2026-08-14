<?php

namespace Mindigo\ExamManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\ExamManagement\Http\Requests\GradeAttemptAnswerRequest;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamSessionAttemptAnswer;
use Mindigo\ExamManagement\Services\ExamGradingService;

class ExamGradingController extends Controller
{
    public function __construct(private readonly ExamGradingService $grading) {}

    public function index(Request $request, ExamSession $session): View
    {
        return view('Mindigo-exam-management::grading.index', [
            'session' => $session->load('version.template'),
            'attempts' => $this->grading->attempts($session, $request->user()),
            'summary' => $this->grading->summary($session, $request->user()),
        ]);
    }

    public function show(Request $request, ExamSession $session, ExamSessionAttempt $attempt): View
    {
        abort_unless((int) $attempt->exam_session_id === (int) $session->id, 404);

        return view('Mindigo-exam-management::grading.show', [
            'attempt' => $this->grading->reviewWorkspace($attempt, $request->user()),
        ]);
    }

    public function grade(
        GradeAttemptAnswerRequest $request,
        ExamSession $session,
        ExamSessionAttempt $attempt,
        ExamSessionAttemptAnswer $answer,
    ): RedirectResponse {
        abort_unless((int) $attempt->exam_session_id === (int) $session->id, 404);
        $this->grading->grade($attempt, $answer, $request->user(), (float) $request->validated('points_awarded'), $request->validated('feedback'));

        return back()->with('success', __('Mindigo-exam-management::app.grading.graded'));
    }

    public function release(Request $request, ExamSession $session, ExamSessionAttempt $attempt): RedirectResponse
    {
        abort_unless((int) $attempt->exam_session_id === (int) $session->id, 404);
        $this->grading->release($attempt, $request->user());

        return back()->with('success', __('Mindigo-exam-management::app.grading.released'));
    }
}
