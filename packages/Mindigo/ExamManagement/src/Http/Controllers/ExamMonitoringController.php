<?php

namespace Mindigo\ExamManagement\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\ExamManagement\Http\Requests\AddAttemptTimeRequest;
use Mindigo\ExamManagement\Http\Requests\MonitorExamSessionRequest;
use Mindigo\ExamManagement\Http\Requests\SendAttemptWarningRequest;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Services\ExamLiveMonitoringService;
use Mindigo\ExamManagement\Services\ExamProctoringService;

class ExamMonitoringController extends Controller
{
    public function __construct(
        private readonly ExamLiveMonitoringService $monitoring,
        private readonly ExamProctoringService $proctoring,
    ) {}

    public function index(MonitorExamSessionRequest $request, ExamSession $session): View
    {
        return view('Mindigo-exam-management::monitoring.index', $this->monitoring->dashboard($session, $request->user(), $request->string('status')->toString()));
    }

    public function data(MonitorExamSessionRequest $request, ExamSession $session): JsonResponse
    {
        $data = $this->monitoring->dashboard($session, $request->user(), $request->string('status')->toString());

        return response()->json([
            'summary' => $data['summary'],
            'html' => view('Mindigo-exam-management::monitoring.partials.candidates', $data)->render(),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function addTime(AddAttemptTimeRequest $request, ExamSession $session, ExamSessionAttempt $attempt): RedirectResponse
    {
        $this->guardSession($session, $attempt);
        $this->proctoring->addTime($attempt, $request->user(), $request->integer('minutes'));

        return back()->with('success', __('Mindigo-exam-management::app.monitoring.time_added'));
    }

    public function retry(Request $request, ExamSession $session, ExamSessionAttempt $attempt): RedirectResponse
    {
        $this->guardSession($session, $attempt);
        $this->proctoring->allowRetry($attempt, $request->user());

        return back()->with('success', __('Mindigo-exam-management::app.monitoring.retry_allowed'));
    }

    public function warning(SendAttemptWarningRequest $request, ExamSession $session, ExamSessionAttempt $attempt): RedirectResponse
    {
        $this->guardSession($session, $attempt);
        $this->proctoring->sendWarning($attempt, $request->user(), $request->validated('message'));

        return back()->with('success', __('Mindigo-exam-management::app.monitoring.warning_sent'));
    }

    public function pause(Request $request, ExamSession $session, ExamSessionAttempt $attempt): RedirectResponse
    {
        $this->guardSession($session, $attempt);
        $this->proctoring->pause($attempt, $request->user());

        return back()->with('success', __('Mindigo-exam-management::app.monitoring.attempt_paused'));
    }

    public function resume(Request $request, ExamSession $session, ExamSessionAttempt $attempt): RedirectResponse
    {
        $this->guardSession($session, $attempt);
        $this->proctoring->resume($attempt, $request->user());

        return back()->with('success', __('Mindigo-exam-management::app.monitoring.attempt_resumed'));
    }

    private function guardSession(ExamSession $session, ExamSessionAttempt $attempt): void
    {
        abort_unless((int) $attempt->exam_session_id === (int) $session->id, 404);
    }
}
