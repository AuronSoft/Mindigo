<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\TeacherCourse\Http\Requests\LessonContentRequest;
use Mindigo\TeacherCourse\Services\CourseDetailService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseLessonController extends Controller
{
    public function __construct(private readonly CourseDetailService $courses) {}

    public function show(LessonContentRequest $request, string $course, int $lesson): View
    {
        return view('teacher-course::catalog.lesson', [
            'lesson' => $this->courses->lesson($request->user(), $course, $lesson),
        ]);
    }

    public function video(LessonContentRequest $request, string $course, int $lesson): StreamedResponse
    {
        return $this->courses->video($request->user(), $course, $lesson);
    }

    public function attachment(
        LessonContentRequest $request,
        string $course,
        int $lesson,
        int $attachment,
    ): StreamedResponse {
        return $this->courses->attachment($request->user(), $course, $lesson, $attachment);
    }
}
