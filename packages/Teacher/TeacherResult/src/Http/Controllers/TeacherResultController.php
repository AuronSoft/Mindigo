<?php

namespace Mindigo\TeacherResult\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherResult\Services\TeacherResultService;

class TeacherResultController extends Controller
{
    public function __construct(private readonly TeacherResultService $service) {}

    public function index()
    {
        session()->forget('url.intended');

        /** @var User $teacher */
        $teacher = Auth::user();
        $keyword = request('q', '');
        $classrooms = $this->service->getMyClassrooms($teacher);
        $selectedClassroom = $this->selectedClassroom($teacher, $classrooms);

        $overview = $this->service->overview($teacher, $selectedClassroom);
        $examResults = $this->service->examResults($teacher, $keyword, $selectedClassroom);
        $studentResults = $this->service->studentResults($teacher, $keyword, $selectedClassroom);

        return view('teacher-result::index', compact(
            'teacher',
            'overview',
            'examResults',
            'studentResults',
            'classrooms',
            'selectedClassroom',
            'keyword'
        ));
    }

    public function byExam(Exam $exam)
    {
        /** @var User $teacher */
        $teacher = Auth::user();
        $classrooms = $this->service->getMyClassrooms($teacher);
        $selectedClassroom = $this->selectedClassroom($teacher, $classrooms, false);
        $result = $this->service->examDetail($teacher, $exam, $selectedClassroom);

        return view('teacher-result::by-exam', compact('exam', 'result', 'selectedClassroom'));
    }

    public function byStudent(User $user)
    {
        abort_if($user->role !== 'student', 404);

        /** @var User $teacher */
        $teacher = Auth::user();
        $classrooms = $this->service->getMyClassrooms($teacher);
        $selectedClassroom = $this->selectedClassroom($teacher, $classrooms, false);
        $detail = $this->service->studentDetail($teacher, $user, $selectedClassroom);

        return view('teacher-result::by-student', compact('user', 'detail', 'selectedClassroom'));
    }

    private function selectedClassroom(User $teacher, Collection $classrooms, bool $defaultFirst = true): ?Classroom
    {
        $requestedId = request()->integer('classroom_id');

        if ($requestedId) {
            return Classroom::query()
                ->where('teacher_id', $teacher->getAuthIdentifier())
                ->whereKey($requestedId)
                ->firstOrFail();
        }

        return $defaultFirst ? $classrooms->first() : null;
    }

    // Chấm điểm thủ công
    public function reviewAttempt(ExamAttempt $attempt)
    {
        /** @var User $teacher */
        $teacher = Auth::user();

        // Chỉ teacher sở hữu đề thi mới được chấm
        abort_unless(
            $teacher->isAdmin() || $attempt->exam?->created_by === (int) $teacher->getAuthIdentifier(),
            403
        );

        $attempt->load(['exam', 'answers.question', 'user']);

        $pendingAnswers = $attempt->answers
            ->where('needs_review', true)
            ->sortBy('id')
            ->values();

        return view('teacher-result::review-attempt', compact('attempt', 'pendingAnswers'));
    }

    public function gradeAttempt(ExamAttempt $attempt)
    {
        /** @var User $teacher */
        $teacher = Auth::user();

        abort_unless(
            $teacher->isAdmin() || $attempt->exam?->created_by === (int) $teacher->getAuthIdentifier(),
            403
        );

        request()->validate([
            'grades' => ['required', 'array'],
            'grades.*' => ['required', 'numeric', 'min:0'],
        ]);

        $this->service->gradeManualAnswers($attempt, request()->input('grades'));

        return redirect()
            ->route('teacher.results.by_exam', ['exam' => $attempt->exam_id])
            ->with('success', 'Đã chấm điểm thành công.');
    }
}
