<?php

namespace Mindigo\ExamManagement\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Http\Requests\AssignExamGraderRequest;
use Mindigo\ExamManagement\Http\Requests\GradeAttemptAnswerRequest;
use Mindigo\ExamManagement\Http\Requests\ResolveGradeAppealRequest;
use Mindigo\ExamManagement\Models\ExamGradeAppeal;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamSessionAttemptAnswer;
use Mindigo\ExamManagement\Models\ExamTemplateQuestion;
use Mindigo\ExamManagement\Services\ExamAdvancedGradingService;
use Mindigo\ExamManagement\Services\ExamGradingService;

class ExamGradingController extends Controller
{
    public function __construct(private readonly ExamGradingService $grading, private readonly ExamAdvancedGradingService $advanced) {}

    public function index(Request $request, ExamSession $session): View
    {
        $isOrganizer = (int) $session->organizer_id === (int) $request->user()->id;

        return view('Mindigo-exam-management::grading.index', [
            'session' => $session->load('version.template'),
            'attempts' => $this->grading->attempts($session, $request->user()),
            'summary' => $this->grading->summary($session, $request->user()),
            'questions' => $session->version->questions()->where('type', 'essay')->get(),
            'availableGraders' => $isOrganizer ? $this->advanced->availableGraders($session, $request->user()) : collect(),
            'assignments' => $session->gradingAssignments()->with('grader')->get(),
            'isOrganizer' => $isOrganizer,
        ]);
    }

    public function question(Request $request, ExamSession $session, ExamTemplateQuestion $question): View
    {
        return view('Mindigo-exam-management::grading.question', ['session' => $session, 'question' => $question, 'answers' => $this->advanced->questionWorkspace($session, $question, $request->user())]);
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
        $this->grading->grade($attempt, $answer, $request->user(), (float) $request->validated('points_awarded'), $request->validated('feedback'), $request->validated('rubric_scores', []), $request->validated('reason'));

        return back()->with('success', __('Mindigo-exam-management::app.grading.graded'));
    }

    public function autosave(GradeAttemptAnswerRequest $request, ExamSession $session, ExamSessionAttempt $attempt, ExamSessionAttemptAnswer $answer): JsonResponse
    {
        abort_unless((int) $attempt->exam_session_id === (int) $session->id, 404);
        $updated = $this->grading->grade($attempt, $answer, $request->user(), (float) $request->validated('points_awarded'), $request->validated('feedback'), $request->validated('rubric_scores', []), $request->validated('reason'));

        return response()->json(['ok' => true, 'score' => $updated->score, 'percentage' => $updated->percentage]);
    }

    public function assign(AssignExamGraderRequest $request, ExamSession $session): RedirectResponse
    {
        $this->advanced->assign($session, $request->user(), User::query()->findOrFail($request->integer('grader_id')));

        return back()->with('success', __('Mindigo-exam-management::app.grading.grader_assigned'));
    }

    public function regrade(Request $request, ExamSession $session): RedirectResponse
    {
        $count = $this->advanced->regrade($session, $request->user());

        return back()->with('success', __('Mindigo-exam-management::app.grading.regraded', ['count' => $count]));
    }

    public function resolveAppeal(ResolveGradeAppealRequest $request, ExamSession $session, ExamGradeAppeal $appeal): RedirectResponse
    {
        abort_unless((int) $appeal->attempt?->exam_session_id === (int) $session->id, 404);
        $this->advanced->resolve($appeal, $request->user(), $request->validated('status'), $request->validated('resolution'));

        return back()->with('success', __('Mindigo-exam-management::app.grading.appeal_completed'));
    }

    public function excel(Request $request, ExamSession $session): Response
    {
        return $this->advanced->spreadsheet($session, $request->user());
    }

    public function pdf(Request $request, ExamSession $session): \Symfony\Component\HttpFoundation\Response
    {
        return $this->advanced->pdf($session, $request->user())->download('exam-results-'.$session->id.'.pdf');
    }

    public function release(Request $request, ExamSession $session, ExamSessionAttempt $attempt): RedirectResponse
    {
        abort_unless((int) $attempt->exam_session_id === (int) $session->id, 404);
        $this->grading->release($attempt, $request->user());

        return back()->with('success', __('Mindigo-exam-management::app.grading.released'));
    }
}
