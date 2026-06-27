<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\TeacherCourse\Http\Requests\ChapterRequest;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;

class ChapterController extends Controller
{
    public function store(ChapterRequest $request, Course $course): RedirectResponse
    {
        $this->authorizeOwnership($course);

        $data = $request->validated();

        $maxOrder = $course->chapters()->max('sort_order') ?? 0;

        $course->chapters()->create([
            'name'       => $data['name'],
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()
            ->route('teacher.courses.show', $course)
            ->with('success', __('teacher-course::app.chapter_created'));
    }

    public function update(ChapterRequest $request, Course $course, Chapter $chapter): RedirectResponse
    {
        $this->authorizeOwnership($course);

        $data = $request->validated();

        $chapter->update(['name' => $data['name']]);

        return redirect()
            ->route('teacher.courses.show', $course)
            ->with('success', __('teacher-course::app.chapter_updated'));
    }

    public function destroy(Course $course, Chapter $chapter): RedirectResponse
    {
        $this->authorizeOwnership($course);

        $chapter->delete();

        return redirect()
            ->route('teacher.courses.show', $course)
            ->with('success', __('teacher-course::app.chapter_deleted'));
    }

    private function authorizeOwnership(Course $course): void
    {
        $user = Auth::user();
        abort_unless(
            $user->isAdmin() || $course->teacher_id === (int) $user->getAuthIdentifier(),
            403,
            __('teacher-course::app.unauthorized_course_action')
        );
    }
}
