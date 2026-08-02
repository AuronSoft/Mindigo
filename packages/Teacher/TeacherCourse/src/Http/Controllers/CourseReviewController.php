<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\TeacherCourse\Http\Requests\CourseReviewModerationRequest;
use Mindigo\TeacherCourse\Http\Requests\CourseReviewReplyRequest;
use Mindigo\TeacherCourse\Http\Requests\CourseReviewRequest;
use Mindigo\TeacherCourse\Http\Requests\ReviewModerationIndexRequest;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseReview;
use Mindigo\TeacherCourse\Services\CourseReviewService;

class CourseReviewController extends Controller
{
    public function __construct(private readonly CourseReviewService $reviews) {}

    public function index(ReviewModerationIndexRequest $request): View
    {
        return view('teacher-course::reviews.index', ['reviews' => $this->reviews->moderationQueue($request->validated()), 'filters' => $request->validated()]);
    }

    public function store(CourseReviewRequest $request, Course $course): RedirectResponse
    {
        $this->reviews->save($course, $request->user(), $request->validated());

        return back()->with('success', __('teacher-course::reviews.saved'));
    }

    public function update(CourseReviewRequest $request, Course $course, CourseReview $review): RedirectResponse
    {
        $this->reviews->save($course, $request->user(), $request->validated(), $review);

        return back()->with('success', __('teacher-course::reviews.updated'));
    }

    public function reply(CourseReviewReplyRequest $request, CourseReview $review): RedirectResponse
    {
        $this->reviews->reply($review, $request->user(), $request->validated('teacher_reply'));

        return back()->with('success', __('teacher-course::reviews.reply_saved'));
    }

    public function moderate(CourseReviewModerationRequest $request, CourseReview $review): RedirectResponse
    {
        $this->reviews->moderate($review, $request->user(), $request->validated());

        return back()->with('success', __('teacher-course::reviews.moderated'));
    }
}
