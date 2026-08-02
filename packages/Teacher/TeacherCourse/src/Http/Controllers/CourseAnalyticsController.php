<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\TeacherCourse\Http\Requests\CourseAnalyticsRequest;
use Mindigo\TeacherCourse\Services\CourseAnalyticsService;

class CourseAnalyticsController extends Controller
{
    public function __invoke(CourseAnalyticsRequest $request, CourseAnalyticsService $analytics): View
    {
        $user = $request->user();
        $data = $user->isAdmin() ? $analytics->admin() : $analytics->teacher($user);

        return view('teacher-course::analytics.index', [
            ...$data, 'activities' => $analytics->activities($user->isTeacher() ? $user : null),
            'isAdminAnalytics' => $user->isAdmin(),
        ]);
    }
}
