<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\TeacherCourse\Http\Requests\CourseCatalogRequest;
use Mindigo\TeacherCourse\Http\Requests\CourseDetailRequest;
use Mindigo\TeacherCourse\Services\CourseCatalogService;
use Mindigo\TeacherCourse\Services\CourseDetailService;

class PublicCourseController extends Controller
{
    public function __construct(
        private readonly CourseCatalogService $catalog,
        private readonly CourseDetailService $details,
    ) {}

    public function index(CourseCatalogRequest $request): View
    {
        $filters = $request->validated();

        return view('teacher-course::catalog.index', [
            'courses' => $this->catalog->catalog($filters),
            'filters' => $filters,
            ...$this->catalog->filters(),
        ]);
    }

    public function show(CourseDetailRequest $request, string $course): View
    {
        return view('teacher-course::catalog.show', [
            'course' => $this->details->detail($request->user(), $course),
        ]);
    }
}
