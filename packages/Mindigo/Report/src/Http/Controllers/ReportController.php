<?php

namespace Mindigo\Report\Http\Controllers;

use Illuminate\Routing\Controller;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\Report\Services\ReportService;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    public function index()
    {
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
        $exams = $this->reportService->getAllExams(20);
        $topExams = $this->reportService->getTopExams(5);

        return view('Mindigo-report::exams', compact('exams', 'topExams'));
    }

    public function examDetail(Exam $exam)
    {
        $report = $this->reportService->getExamReport($exam);

        return view('Mindigo-report::exam-detail', compact('exam', 'report'));
    }

    public function students()
    {
        $students = $this->reportService->getAllStudents(20);
        $topStudents = $this->reportService->getTopStudents(5);

        return view('Mindigo-report::students', compact('students', 'topStudents'));
    }

    public function studentDetail(User $user)
    {
        abort_if($user->role !== 'student', 404);
        $report = $this->reportService->getStudentReport($user);

        return view('Mindigo-report::student-detail', compact('user', 'report'));
    }
}
