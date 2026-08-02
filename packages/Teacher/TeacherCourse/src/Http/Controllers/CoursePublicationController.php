<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Mindigo\TeacherCourse\Http\Requests\CoursePublicationRequest;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Services\CourseService;

class CoursePublicationController extends Controller
{
    public function __construct(private readonly CourseService $courses) {}

    public function update(CoursePublicationRequest $request, Course $course): RedirectResponse
    {
        $this->courses->transition($course, $request->validated('publication_status'), $request->user());

        return to_route('teacher.courses.show', $course)->with('success', __('teacher-course::app.publication_updated'));
    }
}
