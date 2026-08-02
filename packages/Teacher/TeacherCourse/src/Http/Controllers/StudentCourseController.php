<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\TeacherCourse\Http\Requests\LearningActivityRequest;
use Mindigo\TeacherCourse\Http\Requests\StudentCourseRequest;
use Mindigo\TeacherCourse\Services\CourseEnrollmentService;
use Mindigo\TeacherCourse\Services\StudentCourseLearningService;

class StudentCourseController extends Controller
{
    public function __construct(
        private readonly CourseEnrollmentService $enrollments,
        private readonly StudentCourseLearningService $learning,
    ) {}

    public function index(StudentCourseRequest $request): View
    {
        return view('teacher-course::learning.index', ['enrollments' => $this->enrollments->studentCourses($request->user())]);
    }

    public function show(StudentCourseRequest $request, string $course): View
    {
        return view('teacher-course::learning.show', $this->learning->workspace($request->user(), $course));
    }

    public function lesson(StudentCourseRequest $request, string $course, int $lesson): View
    {
        return view('teacher-course::learning.lesson', $this->learning->openLesson($request->user(), $course, $lesson));
    }

    public function activity(LearningActivityRequest $request, string $course, int $lesson): JsonResponse
    {
        $enrollment = $this->learning->recordActivity($request->user(), $course, $lesson, $request->integer('seconds'));

        return response()->json(['saved' => true, 'time_spent_seconds' => $enrollment->time_spent_seconds]);
    }

    public function complete(StudentCourseRequest $request, string $course, int $lesson): RedirectResponse
    {
        $enrollment = $this->learning->completeLesson($request->user(), $course, $lesson);

        return to_route('student.courses.show', $course)
            ->with('success', $enrollment->status === 'completed'
                ? __('teacher-course::learning.course_completed')
                : __('teacher-course::learning.lesson_completed'));
    }
}
