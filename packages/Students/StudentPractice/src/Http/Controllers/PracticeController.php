<?php

namespace Mindigo\StudentPractice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentPractice\Http\Requests\StartPracticeRequest;
use Mindigo\StudentPractice\Http\Requests\SubmitAnswerRequest;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Services\PracticeService;

class PracticeController extends Controller
{
    public function __construct(protected PracticeService $service)
    {
    }

    /**
     * Danh sách bài luyện tập và form bắt đầu.
     */
    public function index(Request $request)
    {
        $formData  = $this->service->formData(auth()->user());
        $questions = $this->service->getQuestions($request->only(['subject', 'topic', 'type', 'difficulty', 'keyword']));

        return view('student-practice::index', compact('formData', 'questions'));
    }

    /**
     * Xem chi tiết một câu hỏi.
     */
    public function show(int $id)
    {
        $question = $this->service->getQuestion($id);

        abort_if($question === null, 404);

        return view('student-practice::show', compact('question'));
    }

    /**
     * Bắt đầu một bài luyện tập mới.
     */
    public function start(StartPracticeRequest $request)
    {
        $attempt = $this->service->startPractice(auth()->user(), $request->validated());

        return redirect()->route('student.practice.attempt', $attempt->id);
    }

    /**
     * Xem bài luyện tập đang tiến hành.
     */
    public function attempt(int $id)
    {
        $attempt = PracticeAttempt::where('student_id', auth()->id())
            ->findOrFail($id);

        if ($attempt->isCompleted()) {
            return redirect()->route('student.practice.result', $attempt->id);
        }

        return view('student-practice::attempt', compact('attempt'));
    }

    /**
     * Gửi câu trả lời cho một câu hỏi.
     */
    public function submitAnswer(SubmitAnswerRequest $request, int $attemptId)
    {
        $attempt = PracticeAttempt::where('student_id', auth()->id())
            ->findOrFail($attemptId);

        abort_if($attempt->isCompleted(), 403, 'This practice session is already completed.');

        $this->service->submitAnswer($attempt, $request->input('question_id'), $request->getAnswer());

        return response()->json([
            'success' => true,
            'message' => 'Answer submitted successfully',
        ]);
    }

    /**
     * Hoàn thành bài luyện tập.
     */
    public function complete(Request $request, int $attemptId)
    {
        $attempt = PracticeAttempt::where('student_id', auth()->id())
            ->findOrFail($attemptId);

        abort_if($attempt->isCompleted(), 403, 'This practice session is already completed.');

        $attempt = $this->service->completePractice($attempt);

        return redirect()->route('student.practice.result', $attempt->id);
    }

    /**
     * Xem kết quả bài luyện tập.
     */
    public function result(int $id)
    {
        $attempt = PracticeAttempt::where('student_id', auth()->id())
            ->findOrFail($id);

        abort_if(!$attempt->isCompleted(), 403, 'This practice session is not completed yet.');

        $details = $this->service->getPracticeDetails($attempt);

        return view('student-practice::result', compact('attempt', 'details'));
    }

    /**
     * Lịch sử luyện tập của học sinh.
     */
    public function history()
    {
        $history = $this->service->getStudentHistory(auth()->user());
        $stats = $this->service->getStudentStats(auth()->user());

        return view('student-practice::history', compact('history', 'stats'));
    }
}
