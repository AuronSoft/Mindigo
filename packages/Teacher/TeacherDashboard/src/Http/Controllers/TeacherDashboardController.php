<?php

namespace Mindigo\TeacherDashboard\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\TeacherDashboard\Services\TeacherDashboardService;

class TeacherDashboardController extends Controller
{
    public function __construct(private readonly TeacherDashboardService $service) {}

    public function index()
    {
        session()->forget('url.intended');

        $teacher = Auth::user();

        $stats         = $this->service->getStats($teacher);
        $myClassrooms  = $this->service->getMyClassrooms($teacher);
        $recentExams   = $this->service->getRecentExams($teacher);
        $recentAttempts= $this->service->getRecentAttempts($teacher);
        $topStudents   = $this->service->getTopStudents($teacher);
        $trend         = $this->service->getWeeklyTrend($teacher);

        return view('teacher-dashboard::dashboard', compact(
            'teacher', 'stats',
            'myClassrooms', 'recentExams',
            'recentAttempts', 'topStudents',
            'trend'
        ));
    }
}
