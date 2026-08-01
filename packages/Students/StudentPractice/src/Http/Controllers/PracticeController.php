<?php

namespace Mindigo\StudentPractice\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Mindigo\StudentPractice\Http\Requests\CompletePracticeRequest;
use Mindigo\StudentPractice\Http\Requests\PracticeIndexRequest;
use Mindigo\StudentPractice\Http\Requests\StartPracticeRequest;
use Mindigo\StudentPractice\Http\Requests\SubmitAnswerRequest;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Services\PracticeService;

class PracticeController extends Controller
{
    public function __construct(private readonly PracticeService $service) {}

    public function index(PracticeIndexRequest $request): View
    {
        return view('student-practice::index', [
            'formData' => $this->service->formData($request->user()),
            'questions' => $this->service->getQuestions(
                $request->validated()
            ),
        ]);
    }

    public function show(int $question): View
    {
        $question = $this->service->getQuestion($question);
        abort_if($question === null, 404);

        return view('student-practice::show', compact('question'));
    }

    public function start(StartPracticeRequest $request): RedirectResponse
    {
        $attempt = $this->service->startPractice($request->user(), $request->validated());

        return to_route('student.practice.attempt', $attempt);
    }

    public function attempt(Request $request, PracticeAttempt $attempt): View|RedirectResponse
    {
        Gate::forUser($request->user())->authorize('view', $attempt);
        $attempt = $this->service->reconcileAttempt($attempt);
        if ($attempt->isExpired()) {
            return to_route('student.practice.index')
                ->withErrors(['attempt' => __('student-practice::app.errors.session_expired')]);
        }
        if ($attempt->isCompleted()) {
            return to_route('student.practice.result', $attempt);
        }

        $attempt->loadMissing('answers.question');

        return view('student-practice::attempt', compact('attempt'));
    }

    public function submitAnswer(
        SubmitAnswerRequest $request,
        PracticeAttempt $attempt,
    ): JsonResponse {
        $answer = $this->service->submitAnswer(
            $attempt,
            $request->integer('question_id'),
            $request->answer()
        );

        return response()->json([
            'message' => __('student-practice::app.messages.answer_saved'),
            'is_correct' => $answer->is_correct,
        ]);
    }

    public function complete(CompletePracticeRequest $request, PracticeAttempt $attempt): RedirectResponse
    {
        $completed = $this->service->completePractice($attempt);

        return to_route('student.practice.result', $completed)
            ->with('success', __('student-practice::app.messages.completed'));
    }

    public function result(Request $request, PracticeAttempt $attempt): View
    {
        Gate::forUser($request->user())->authorize('view', $attempt);
        abort_unless($attempt->isCompleted(), 404);

        return view('student-practice::result', [
            'details' => $this->service->getPracticeDetails($attempt),
        ]);
    }

    public function history(Request $request): View
    {
        return view('student-practice::history', [
            'history' => $this->service->getStudentHistory($request->user()),
            'stats' => $this->service->getStudentStats($request->user()),
        ]);
    }
}
