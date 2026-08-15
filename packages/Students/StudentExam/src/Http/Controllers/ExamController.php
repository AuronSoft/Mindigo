<?php

namespace Mindigo\StudentExam\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\ExamManagement\Services\ExamCutoverService;
use Mindigo\StudentExam\Http\Requests\AutosaveExamAnswerRequest;
use Mindigo\StudentExam\Http\Requests\SubmitExamRequest;
use Mindigo\StudentExam\Services\ExamService;

class ExamController extends Controller
{
    public function __construct(protected ExamService $service, private ExamCutoverService $cutover) {}

    public function index(Request $request)
    {
        if ($this->cutover->prefersNew($request->user())) {
            return redirect()->route('student.exam-sessions.index');
        }
        $studentId = Auth::id();

        $data = $this->service->getExamsForStudent($studentId);

        return view('student-exam::index', $data);
    }

    public function start(Exam $exam)
    {
        $this->authorizeLegacyWrite();
        $studentId = Auth::id();
        $attempt = $this->service->startAttempt($exam, $studentId);

        return redirect()->route('student.exams.take', $attempt);
    }

    public function take(ExamAttempt $attempt)
    {
        abort_unless((int) $attempt->user_id === (int) Auth::id(), 403);
        abort_unless($attempt->exam, 404);
        abort_unless($this->service->isEnrolledInExamClassroom($attempt->exam, Auth::id()), 403);

        if (in_array($attempt->status, ['submitted', 'expired'])) {
            return redirect()->route('student.exams.result', $attempt);
        }

        if ($attempt->expires_at && now()->gt($attempt->expires_at)) {
            $this->service->autoSubmit($attempt);

            return redirect()->route('student.exams.result', $attempt)
                ->with('warning', __('student-exam::app.time_expired'));
        }

        $questions = $this->service->getQuestionsForAttempt($attempt);
        $savedAnswers = $this->service->getSavedAnswers($attempt);

        return view('student-exam::take', compact('attempt', 'questions', 'savedAnswers'));
    }

    public function submit(SubmitExamRequest $request, ExamAttempt $attempt)
    {
        $this->authorizeLegacyWrite();
        $attempt = $this->service->submitAttempt($attempt, $request->validated());

        if ($attempt->status === 'expired') {
            return redirect()->route('student.exams.result', $attempt)
                ->with('warning', __('student-exam::app.time_expired'));
        }

        return redirect()->route('student.exams.result', $attempt)
            ->with('success', __('student-exam::app.submitted_success'));
    }

    public function result(ExamAttempt $attempt)
    {
        abort_unless((int) $attempt->user_id === (int) Auth::id(), 403);

        abort_unless(
            in_array($attempt->status, ['submitted', 'expired']),
            403,
            __('student-exam::app.not_submitted_yet')
        );

        $result = $this->service->getResult($attempt);

        return view('student-exam::result', compact('attempt', 'result'));
    }

    public function autosave(AutosaveExamAnswerRequest $request, ExamAttempt $attempt): JsonResponse
    {
        $this->authorizeLegacyWrite();
        $saved = $this->service->saveAnswer(
            $attempt,
            $request->integer('question_id'),
            $request->validated('answer')
        );

        return response()->json(
            ['ok' => $saved, 'message' => $saved ? null : __('student-exam::app.attempt_locked')],
            $saved ? 200 : 409
        );
    }

    public function heartbeat(ExamAttempt $attempt): JsonResponse
    {
        $this->authorizeLegacyWrite();
        abort_unless((int) $attempt->user_id === (int) Auth::id(), 403);
        abort_unless($attempt->status === 'in_progress', 422);

        $active = $this->service->recordActivity($attempt);

        return response()->json(['ok' => $active], $active ? 200 : 409);
    }

    private function authorizeLegacyWrite(): void
    {
        abort_unless($this->cutover->legacyWritable(Auth::user()), 423, 'The legacy exam module is read-only after cutover.');
    }
}
