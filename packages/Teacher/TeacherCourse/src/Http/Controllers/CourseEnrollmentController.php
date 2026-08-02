<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Mindigo\TeacherCourse\Http\Requests\CourseAssignmentRequest;
use Mindigo\TeacherCourse\Http\Requests\SelfEnrollRequest;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Services\CourseEnrollmentService;

class CourseEnrollmentController extends Controller
{
    public function __construct(private readonly CourseEnrollmentService $enrollments) {}

    public function store(SelfEnrollRequest $request, string $course): RedirectResponse
    {
        $enrollment = $this->enrollments->selfEnroll($request->user(), $course);

        return to_route('student.courses.show', $enrollment->course()->value('slug'))
            ->with('success', __('teacher-course::learning.enrolled_successfully'));
    }

    public function assign(CourseAssignmentRequest $request, Course $course): RedirectResponse
    {
        $count = $this->enrollments->assignToClassrooms($course, $request->user(), $request->validated('classroom_ids'));

        return to_route('teacher.courses.show', $course)
            ->with('success', trans_choice('teacher-course::learning.assigned_successfully', $count, ['count' => $count]));
    }
}
