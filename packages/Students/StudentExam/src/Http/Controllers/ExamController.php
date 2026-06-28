<?php

namespace Mindigo\StudentExam\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\StudentExam\Http\Requests\SubmitExamRequest;
use Mindigo\StudentExam\Services\ExamService;

class ExamController extends Controller
{
    public function __construct(protected ExamService $service)
    {
    }

    /**
     * Danh sách đề thi của các lớp HS đang học.
     * Chia 3 nhóm: sắp mở / đang mở / đã làm.
     */
    public function index(Request $request)
    {
        $studentId = auth()->id();

        $data = $this->service->getExamsForStudent($studentId);

        return view('student-exam::index', $data);
    }

    /**
     * Bắt đầu làm bài: tạo ExamAttempt mới.
     * Chặn nếu: chưa mở, đã quá hạn, vượt số lần cho phép, HS không thuộc lớp.
     */
    public function start(Exam $exam)
    {
        $studentId = auth()->id();

        // Kiểm tra HS thuộc lớp có đề này
        abort_unless(
            $this->service->isEnrolledInExamClassroom($exam, $studentId),
            403,
            __('student-exam::app.not_enrolled')
        );

        // Kiểm tra đề đang trong thời gian mở
        abort_unless(
            $this->service->isAvailable($exam),
            403,
            __('student-exam::app.exam_not_available')
        );

        // Kiểm tra số lần thử
        abort_if(
            $this->service->hasExceededAttempts($exam, $studentId),
            403,
            __('student-exam::app.max_attempts_reached')
        );

        $attempt = $this->service->startAttempt($exam, $studentId);

        return redirect()->route('student.exams.take', $attempt);
    }

    /**
     * Trang làm bài — render câu hỏi, đồng hồ đếm ngược.
     */
    public function take(ExamAttempt $attempt)
    {
        // Chỉ chủ nhân mới được làm
        abort_unless($attempt->user_id === auth()->id(), 403);

        // Chặn xem lại đề đang chờ chấm / đã nộp
        abort_if(
            in_array($attempt->status, ['submitted', 'graded']),
            302,
            redirect()->route('student.exams.result', $attempt)
        );

        // Chặn nếu hết giờ (server-side)
        if ($attempt->expires_at && now()->gt($attempt->expires_at)) {
            $this->service->autoSubmit($attempt);
            return redirect()->route('student.exams.result', $attempt)
                ->with('warning', __('student-exam::app.time_expired'));
        }

        $questions = $this->service->getQuestionsForAttempt($attempt);
        $savedAnswers = $this->service->getSavedAnswers($attempt);

        return view('student-exam::take', compact('attempt', 'questions', 'savedAnswers'));
    }

    /**
     * Nộp bài: lưu đáp án, chấm điểm tự động (trắc nghiệm), cập nhật status.
     */
    public function submit(SubmitExamRequest $request, ExamAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);

        abort_if(
            in_array($attempt->status, ['submitted', 'graded']),
            403,
            __('student-exam::app.already_submitted')
        );

        $this->service->submitAttempt($attempt, $request->validated());

        return redirect()->route('student.exams.result', $attempt)
            ->with('success', __('student-exam::app.submitted_success'));
    }

    /**
     * Xem kết quả / review sau khi nộp.
     */
    public function result(ExamAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);

        abort_unless(
            in_array($attempt->status, ['submitted', 'graded']),
            403,
            __('student-exam::app.not_submitted_yet')
        );

        $result = $this->service->getResult($attempt);

        return view('student-exam::result', compact('attempt', 'result'));
    }
}