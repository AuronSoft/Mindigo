<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Mindigo\TeacherCourse\Http\Requests\LessonRequest;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\Lesson;
use Mindigo\TeacherCourse\Services\CurriculumService;

class LessonController extends Controller
{
    public function __construct(private readonly CurriculumService $curriculum) {}

    public function create(Request $request, Course $course, Chapter $chapter): View
    {
        Gate::authorize('update', $course);

        return view('teacher-course::lessons.create', [
            'course' => $course,
            'chapter' => $chapter,
            ...$this->curriculum->lessonFormData($course, (int) $request->user()->getAuthIdentifier()),
        ]);
    }

    public function store(LessonRequest $request, Course $course, Chapter $chapter): RedirectResponse
    {
        $this->curriculum->createLesson(
            $chapter,
            $request->safe()->except(['video', 'attachments', 'remove_video']),
            $request->file('video'),
            $request->file('attachments', []),
        );

        return to_route('teacher.courses.show', $course)->with('success', __('teacher-course::app.lesson_created'));
    }

    public function edit(Request $request, Lesson $lesson): View
    {
        Gate::authorize('update', $lesson);
        $chapter = $lesson->chapter;
        $course = $chapter->course;

        return view('teacher-course::lessons.edit', [
            'course' => $course,
            'chapter' => $chapter,
            'lesson' => $lesson,
            ...$this->curriculum->lessonFormData($course, (int) $request->user()->getAuthIdentifier(), $lesson),
        ]);
    }

    public function update(LessonRequest $request, Lesson $lesson): RedirectResponse
    {
        $course = $lesson->chapter->course;
        $this->curriculum->updateLesson(
            $lesson,
            $request->safe()->except(['video', 'attachments', 'remove_video']),
            $request->file('video'),
            $request->file('attachments', []),
            $request->boolean('remove_video'),
        );

        return to_route('teacher.courses.show', $course)->with('success', __('teacher-course::app.lesson_updated'));
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        Gate::authorize('delete', $lesson);
        $course = $lesson->chapter->course;
        $this->curriculum->deleteLesson($lesson);

        return to_route('teacher.courses.show', $course)->with('success', __('teacher-course::app.lesson_deleted'));
    }
}
