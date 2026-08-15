<?php

namespace Mindigo\Report\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Services\ExamCutoverService;
use Mindigo\Report\Services\ReportService;
use Mindigo\TeacherClassroom\Models\Classroom;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService, private readonly ExamCutoverService $cutover) {}

    public function index()
    {
        if ($this->usesOperationalExamReports()) {
            return redirect()->route('admin.exam-operations');
        }

        $overview = $this->reportService->getOverviewStats();
        $trend = $this->reportService->getAttemptTrend(30);
        $topExams = $this->reportService->getTopExams(5);
        $topStudents = $this->reportService->getTopStudents(5);
        $subjectBreakdown = $this->reportService->getSubjectBreakdown();
        $scoreDistribution = $this->reportService->getScoreDistribution();

        return view('Mindigo-report::index', compact(
            'overview', 'trend', 'topExams', 'topStudents',
            'subjectBreakdown', 'scoreDistribution'
        ));
    }

    public function exams()
    {
        if ($this->cutover->mode() === ExamCutoverService::MODE_NEW) {
            return redirect()->route('admin.exam-operations');
        }
        $exams = $this->reportService->getAllExams(20);
        $topExams = $this->reportService->getTopExams(5);

        return view('Mindigo-report::exams', compact('exams', 'topExams'));
    }

    public function examDetail(Exam $exam)
    {
        if ($this->cutover->mode() === ExamCutoverService::MODE_NEW) {
            return redirect()->route('admin.exam-operations');
        }
        $report = $this->reportService->getExamReport($exam);

        return view('Mindigo-report::exam-detail', compact('exam', 'report'));
    }

    public function students()
    {
        if ($this->usesOperationalExamReports()) {
            return redirect()->route('admin.exam-operations');
        }

        $students = $this->reportService->getAllStudents(20);
        $topStudents = $this->reportService->getTopStudents(5);

        return view('Mindigo-report::students', compact('students', 'topStudents'));
    }

    public function studentDetail(User $user)
    {
        if ($this->usesOperationalExamReports()) {
            return redirect()->route('admin.exam-operations');
        }

        abort_if($user->role !== 'student', 404);
        $report = $this->reportService->getStudentReport($user);

        return view('Mindigo-report::student-detail', compact('user', 'report'));
    }

    public function teacher()
    {
        /** @var User $teacher */
        $teacher = Auth::user();
        $classrooms = $this->reportService->getTeacherClassrooms($teacher);
        $selectedClassroom = $this->selectedTeacherClassroom($teacher);
        $period = request()->integer('period', 30);
        $period = in_array($period, [7, 30, 90], true) ? $period : 30;

        $report = $this->reportService->getTeacherReport($teacher, $selectedClassroom, $period);

        return view('Mindigo-report::teacher', compact(
            'teacher',
            'classrooms',
            'selectedClassroom',
            'period',
            'report'
        ));
    }

    private function selectedTeacherClassroom(User $teacher): ?Classroom
    {
        $classroomId = request()->integer('classroom_id');

        if (! $classroomId) {
            return null;
        }

        return Classroom::query()
            ->where('teacher_id', $teacher->getAuthIdentifier())
            ->whereKey($classroomId)
            ->firstOrFail();
    }

    private function usesOperationalExamReports(): bool
    {
        return $this->cutover->mode() === ExamCutoverService::MODE_NEW;
    }
}
