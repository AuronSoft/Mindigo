<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\TeacherCourse\Http\Requests\CourseSuggestionRequest;
use Mindigo\TeacherCourse\Http\Requests\WishlistRequest;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Services\CourseDiscoveryService;
use Mindigo\TeacherCourse\Services\CourseRecommendationService;
use Mindigo\TeacherCourse\Services\CourseSearchService;

class CourseDiscoveryController extends Controller
{
    public function __construct(
        private readonly CourseDiscoveryService $discovery,
        private readonly CourseRecommendationService $recommendations,
        private readonly CourseSearchService $search,
    ) {}

    public function wishlist(WishlistRequest $request): View
    {
        $courses = $this->discovery->wishlist($request->user());

        return view('teacher-course::discovery.collection', [
            'title' => __('teacher-course::discovery.wishlist'),
            'description' => __('teacher-course::discovery.wishlist_description'),
            'courses' => $courses,
            'wishlistedIds' => $courses->pluck('id')->all(),
        ]);
    }

    public function store(WishlistRequest $request, Course $course): RedirectResponse
    {
        $this->discovery->addWishlist($request->user(), $course);

        return back()->with('success', __('teacher-course::discovery.wishlist_added'));
    }

    public function destroy(WishlistRequest $request, Course $course): RedirectResponse
    {
        $this->discovery->removeWishlist($request->user(), $course);

        return back()->with('success', __('teacher-course::discovery.wishlist_removed'));
    }

    public function recent(WishlistRequest $request): View
    {
        return view('teacher-course::discovery.collection', [
            'title' => __('teacher-course::discovery.recent_title'),
            'description' => __('teacher-course::discovery.recent_description'),
            'courses' => $this->discovery->recentlyViewed($request->user()),
        ]);
    }

    public function recommended(WishlistRequest $request): View
    {
        return view('teacher-course::discovery.collection', [
            'title' => __('teacher-course::discovery.recommended_title'),
            'description' => __('teacher-course::discovery.recommended_description'),
            'courses' => $this->recommendations->forStudent($request->user()),
        ]);
    }

    public function suggestions(CourseSuggestionRequest $request): JsonResponse
    {
        return response()->json(['data' => $this->search->suggestions($request->validated('query'))->values()]);
    }
}
