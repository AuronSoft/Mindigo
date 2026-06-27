<?php

namespace Mindigo\StudentDashboard\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\StudentDashboard\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $service)
    {
    }

    public function index()
    {
        session()->forget('url.intended');

        /** @var \Mindigo\Auth\Models\User $student */
        $student = Auth::user();

        $classroomIds        = $this->service->classroomIds($student);
        $stats               = $this->service->getStats($student, $classroomIds);
        $myClassrooms        = $this->service->getMyClassrooms($student);
        $upcomingAssignments = $this->service->getUpcomingAssignments($student, $classroomIds);
        $openExams           = $this->service->getOpenExams();
        $recentResults       = $this->service->getRecentResults($student);

        return view('student-dashboard::dashboard', compact(
            'student',
            'stats',
            'myClassrooms',
            'upcomingAssignments',
            'openExams',
            'recentResults',
        ));
    }
}
