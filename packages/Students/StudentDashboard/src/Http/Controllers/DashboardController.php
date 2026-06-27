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

        $classroomIds   = $this->service->classroomIds($student);
        $stats          = $this->service->getStudyStats($student, $classroomIds);
        $weekStrip      = $this->service->getWeekStrip();
        $activeTasks    = $this->service->getActiveTasks($student, $classroomIds);
        $courseStats    = $this->service->getCourseStats($student, $classroomIds);
        $weeklyActivity = $this->service->getWeeklyActivity($student);
        $recentActivity = $this->service->getRecentActivity($student);

        return view('student-dashboard::dashboard', compact(
            'student',
            'stats',
            'weekStrip',
            'activeTasks',
            'courseStats',
            'weeklyActivity',
            'recentActivity',
        ));
    }
}
