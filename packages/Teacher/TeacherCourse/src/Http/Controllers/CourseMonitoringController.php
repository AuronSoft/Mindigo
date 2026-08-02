<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\TeacherCourse\Http\Requests\CourseMonitoringRequest;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Services\CourseMonitoringService;

class CourseMonitoringController extends Controller
{
    public function __construct(private readonly CourseMonitoringService $monitoring) {}

    public function __invoke(CourseMonitoringRequest $request, Course $course): View
    {
        return view('teacher-course::monitor', $this->monitoring->report(
            $course,
            $request->validated(),
        ));
    }
}
