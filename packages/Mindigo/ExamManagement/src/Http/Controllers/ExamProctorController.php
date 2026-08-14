<?php

namespace Mindigo\ExamManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Mindigo\ExamManagement\Http\Requests\ProctorNoteRequest;
use Mindigo\ExamManagement\Http\Requests\TerminateAttemptRequest;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Services\ExamProctoringService;

class ExamProctorController extends Controller
{
    public function __construct(private readonly ExamProctoringService $proctoring) {}

    public function note(ProctorNoteRequest $request, ExamSession $session, ExamSessionAttempt $attempt): RedirectResponse
    {
        $this->guardSession($session, $attempt);
        $this->proctoring->addNote($attempt, $request->user(), $request->validated('note'));

        return back()->with('success', __('Mindigo-exam-management::app.proctoring.note_added'));
    }

    public function terminate(TerminateAttemptRequest $request, ExamSession $session, ExamSessionAttempt $attempt): RedirectResponse
    {
        $this->guardSession($session, $attempt);
        $this->proctoring->terminate($attempt, $request->user(), $request->validated('reason'));

        return back()->with('success', __('Mindigo-exam-management::app.proctoring.attempt_terminated'));
    }

    private function guardSession(ExamSession $session, ExamSessionAttempt $attempt): void
    {
        abort_unless((int) $attempt->exam_session_id === (int) $session->id, 404);
    }
}
