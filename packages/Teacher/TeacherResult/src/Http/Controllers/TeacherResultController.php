<?php

namespace Mindigo\TeacherResult\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\TeacherResult\Services\TeacherResultService;

class TeacherResultController extends Controller
{
    public function __construct(private readonly TeacherResultService $service) {}

    public function index()
    {
        session()->forget('url.intended');

        /** @var \Mindigo\Auth\Models\User $teacher */
        $teacher  = Auth::user();
        $keyword  = request('q', '');

        $overview      = $this->service->overview($teacher);
        $examResults   = $this->service->examResults($teacher, $keyword);
        $studentResults= $this->service->studentResults($teacher, $keyword);
        $classrooms    = $this->service->getMyClassrooms($teacher);

        return view('teacher-result::index', compact(
            'teacher', 'overview', 'examResults', 'studentResults', 'classrooms', 'keyword'
        ));
    }

    public function byExam(Exam $exam)
    {
        /** @var \Mindigo\Auth\Models\User $teacher */
        $teacher = Auth::user();
        $result  = $this->service->examDetail($teacher, $exam);

        return view('teacher-result::by-exam', compact('exam', 'result'));
    }

    public function byStudent(User $user)
    {
        abort_if($user->role !== 'student', 404);

        /** @var \Mindigo\Auth\Models\User $teacher */
        $teacher = Auth::user();
        $detail  = $this->service->studentDetail($teacher, $user);

        return view('teacher-result::by-student', compact('user', 'detail'));
    }
}
