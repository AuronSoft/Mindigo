<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Mindigo\TeacherCourse\Http\Requests\FeaturedCourseRequest;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Services\CourseDiscoveryService;

class FeaturedCourseController extends Controller
{
    public function __invoke(FeaturedCourseRequest $request, Course $course, CourseDiscoveryService $discovery): RedirectResponse
    {
        $data = $request->validated();
        $discovery->setFeatured($course, (bool) $data['is_featured'], (int) ($data['featured_order'] ?? 0));

        return back()->with('success', __('teacher-course::discovery.featured_updated'));
    }
}
