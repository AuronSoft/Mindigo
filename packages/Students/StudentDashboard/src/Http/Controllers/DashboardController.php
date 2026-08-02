<?php

namespace Mindigo\StudentDashboard\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\Auth\Models\User;
use Mindigo\StudentDashboard\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $service) {}

    public function index()
    {
        session()->forget('url.intended');

        /** @var User $student */
        $student = Auth::user();

        $classroomIds = $this->service->classroomIds($student);
        $stats = $this->service->getStudyStats($student, $classroomIds);
        $weekStrip = $this->service->getWeekStrip();
        $activeTasks = $this->service->getActiveTasks($student, $classroomIds);
        $courseStats = $this->service->getCourseStats($student, $classroomIds);
        $weeklyActivity = $this->service->getWeeklyActivity($student);
        $recentActivity = $this->service->getRecentActivity($student);
        $monthCalendar = $this->service->getMonthCalendar($activeTasks);
        $todayTasks = $activeTasks->filter(fn ($task) => $task->at?->isToday())->values();
        $upcomingExams = $activeTasks->where('type', 'exam')->take(3)->values();
        $assignments = $activeTasks->where('type', 'assignment')->take(4)->values();
        $activeCourses = $this->service->activeCourses($student);

        return view('student-dashboard::dashboard', compact(
            'student',
            'stats',
            'weekStrip',
            'activeTasks',
            'courseStats',
            'weeklyActivity',
            'recentActivity',
            'monthCalendar',
            'todayTasks',
            'upcomingExams',
            'assignments',
            'classroomIds',
            'activeCourses',
        ));
    }
}
