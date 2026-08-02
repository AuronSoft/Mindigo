<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\TeacherCourse\Http\Requests\CourseCatalogRequest;
use Mindigo\TeacherCourse\Http\Requests\CourseDetailRequest;
use Mindigo\TeacherCourse\Services\CourseCatalogService;
use Mindigo\TeacherCourse\Services\CourseDetailService;
use Mindigo\TeacherCourse\Services\CourseDiscoveryService;
use Mindigo\TeacherCourse\Services\CourseRecommendationService;
use Mindigo\TeacherCourse\Services\CourseSearchService;

class PublicCourseController extends Controller
{
    public function __construct(
        private readonly CourseCatalogService $catalog,
        private readonly CourseDetailService $details,
        private readonly CourseDiscoveryService $discovery,
        private readonly CourseRecommendationService $recommendations,
        private readonly CourseSearchService $search,
    ) {}

    public function index(CourseCatalogRequest $request): View
    {
        $filters = $request->validated();
        $this->search->record($request->user(), $filters['search'] ?? null);

        return view('teacher-course::catalog.index', [
            'courses' => $this->catalog->catalog($filters),
            'filters' => $filters,
            ...$this->catalog->filters(),
            'featuredCourses' => $this->discovery->featured(),
            'trendingCourses' => $this->discovery->trending(),
            'recentCourses' => $request->user()?->isStudent() ? $this->discovery->recentlyViewed($request->user(), 4) : collect(),
            'recommendedCourses' => $request->user()?->isStudent() ? $this->recommendations->forStudent($request->user(), 4) : collect(),
            'wishlistedIds' => $this->discovery->wishlistIds($request->user()),
            'popularKeywords' => $this->search->popular(),
            'recentSearches' => $this->search->recent($request->user()),
        ]);
    }

    public function show(CourseDetailRequest $request, string $course): View
    {
        $course = $this->details->detail($request->user(), $course);

        return view('teacher-course::catalog.show', [
            'course' => $course,
            'relatedCourses' => $this->discovery->related($course),
            'wishlistedIds' => $this->discovery->wishlistIds($request->user()),
        ]);
    }
}
