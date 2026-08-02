<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Mindigo\TeacherCourse\Http\Requests\CurriculumOrderRequest;
use Mindigo\TeacherCourse\Http\Requests\DuplicateCourseRequest;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Services\CourseService;
use Mindigo\TeacherCourse\Services\CurriculumService;

class CourseBuilderController extends Controller
{
    public function __construct(
        private readonly CourseService $courses,
        private readonly CurriculumService $curriculum,
    ) {}

    public function duplicate(DuplicateCourseRequest $request, Course $course): RedirectResponse
    {
        $copy = $this->courses->duplicate($course, $request->user());

        return to_route('teacher.courses.show', $copy)->with('success', __('teacher-course::publishing.duplicated'));
    }

    public function reorder(CurriculumOrderRequest $request, Course $course): JsonResponse
    {
        $this->curriculum->reorder($course, $request->validated('chapters'));

        return response()->json(['saved' => true, 'message' => __('teacher-course::publishing.order_saved')]);
    }
}
