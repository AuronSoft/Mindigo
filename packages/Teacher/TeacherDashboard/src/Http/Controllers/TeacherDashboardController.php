<?php

namespace Mindigo\TeacherDashboard\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherDashboard\Services\TeacherDashboardService;

class TeacherDashboardController extends Controller
{
    public function __construct(private readonly TeacherDashboardService $service) {}

    public function index()
    {
        session()->forget('url.intended');

        /** @var User $teacher */
        $teacher = Auth::user();

        $stats = $this->service->getStats($teacher);
        $myClassrooms = $this->service->getMyClassrooms($teacher);
        $recentExams = $this->service->getRecentExams($teacher);
        $recentAttempts = $this->service->getRecentAttempts($teacher);
        $topStudents = $this->service->getTopStudents($teacher);
        $trend = $this->service->getWeeklyTrend($teacher);
        $assignmentStats = $this->service->getAssignmentStats($teacher);
        $recentAssignments = $this->service->getRecentAssignments($teacher);
        $recentAssignmentSubmissions = $this->service->getRecentAssignmentSubmissions($teacher);
        $upcomingActivities = $this->service->getUpcomingActivities($teacher);
        $performance = $this->service->getPerformanceOverview($teacher);

        return view('teacher-dashboard::dashboard', compact(
            'teacher', 'stats',
            'myClassrooms', 'recentExams',
            'recentAttempts', 'topStudents',
            'trend', 'assignmentStats',
            'recentAssignments', 'recentAssignmentSubmissions',
            'upcomingActivities', 'performance'
        ));
    }
}
