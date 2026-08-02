<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Mindigo\TeacherCourse\Http\Requests\ChapterRequest;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Services\CurriculumService;

class ChapterController extends Controller
{
    public function __construct(private readonly CurriculumService $curriculum) {}

    public function store(ChapterRequest $request, Course $course): RedirectResponse
    {
        $this->curriculum->createChapter($course, $request->validated());

        return to_route('teacher.courses.show', $course)->with('success', __('teacher-course::app.chapter_created'));
    }

    public function update(ChapterRequest $request, Course $course, Chapter $chapter): RedirectResponse
    {
        $this->curriculum->updateChapter($chapter, $request->validated());

        return to_route('teacher.courses.show', $course)->with('success', __('teacher-course::app.chapter_updated'));
    }

    public function destroy(Course $course, Chapter $chapter): RedirectResponse
    {
        Gate::authorize('delete', $chapter);
        $this->curriculum->deleteChapter($chapter);

        return to_route('teacher.courses.show', $course)->with('success', __('teacher-course::app.chapter_deleted'));
    }
}
