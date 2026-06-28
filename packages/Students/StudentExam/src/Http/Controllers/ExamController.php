<?php

namespace Mindigo\StudentExam\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\StudentExam\Http\Requests\SubmitExamRequest;
use Mindigo\StudentExam\Services\ExamService;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;

class ExamController extends Controller
{
    public function __construct(protected ExamService $service)
    {
    }

   
    public function index(Request $request)
    {
        $studentId = auth()->id();

        $data = $this->service->getExamsForStudent($studentId);

        return view('student-exam::index', $data);
    }

    
    public function start(Exam $exam)
    {
        $studentId = auth()->id();

       
        abort_unless(
            $this->service->isEnrolledInExamClassroom($exam, $studentId),
            403,
            __('student-exam::app.not_enrolled')
        );

        
        abort_unless(
            $this->service->isAvailable($exam),
            403,
            __('student-exam::app.exam_not_available')
        );

       
        abort_if(
            $this->service->hasExceededAttempts($exam, $studentId),
            403,
            __('student-exam::app.max_attempts_reached')
        );

        $attempt = $this->service->startAttempt($exam, $studentId);

        return redirect()->route('student.exams.take', $attempt);
    }

   
    public function take(ExamAttempt $attempt)
    {
        
        abort_unless($attempt->user_id === auth()->id(), 403);

        
        abort_if(
            in_array($attempt->status, ['submitted', 'graded']),
            302,
            redirect()->route('student.exams.result', $attempt)
        );

        
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

     public function autosave(Request $request, ExamAttempt $attempt): \Illuminate\Http\JsonResponse
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        abort_if(in_array($attempt->status, ['submitted', 'graded']), 403);

        if ($attempt->expires_at && now()->gt($attempt->expires_at)) {
            return response()->json(['ok' => false, 'reason' => 'expired'], 403);
        }

        $validated = $request->validate([
            'question_id'  => ['required'],
            'answer_value' => ['nullable', 'string', 'max:65535'],
        ]);

        ExamAttemptAnswer::updateOrCreate(
            [
                'attempt_id'  => $attempt->id,
                'question_id' => $validated['question_id'],
            ],
            [
                'answer_value' => $validated['answer'],
            ]
        );

        return response()->json(['ok' => true]);
    }
}