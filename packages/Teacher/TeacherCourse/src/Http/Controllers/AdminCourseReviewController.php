<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Mindigo\TeacherCourse\Http\Requests\AdminCourseReviewActionRequest;
use Mindigo\TeacherCourse\Http\Requests\AdminCourseReviewIndexRequest;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Services\AdminCourseReviewService;

class AdminCourseReviewController extends Controller
{
    public function __construct(private readonly AdminCourseReviewService $reviews) {}

    public function index(AdminCourseReviewIndexRequest $request): View
    {
        $filters = $request->safe()->only(['search', 'teacher_id', 'sort']);

        return view('teacher-course::admin-reviews.index', [
            'courses' => $this->reviews->queue($filters),
            'teachers' => $this->reviews->teachers(),
            'filters' => $filters,
        ]);
    }

    public function show(Course $course): View
    {
        Gate::authorize('view', $course);

        return view('teacher-course::admin-reviews.show', [
            'course' => $this->reviews->detail($course),
        ]);
    }

    public function update(AdminCourseReviewActionRequest $request, Course $course): RedirectResponse
    {
        $data = $request->validated();
        if ($data['action'] === 'approve') {
            $this->reviews->approve($course, $request->user(), $data['review_note'] ?? null);
        } else {
            $this->reviews->requestChanges($course, $request->user(), $data['review_note']);
        }

        return to_route('admin.course-publication-reviews.index')
            ->with('success', __('teacher-course::admin-review.action_completed'));
    }
}
