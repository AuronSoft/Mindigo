<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;

class ChapterController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeOwnership($course);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $maxOrder = $course->chapters()->max('sort_order') ?? 0;

        $course->chapters()->create([
            'name'       => $data['name'],
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()
            ->route('teacher.courses.show', $course)
            ->with('success', 'Đã thêm chương học mới.');
    }

    public function update(Request $request, Course $course, Chapter $chapter): RedirectResponse
    {
        $this->authorizeOwnership($course);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $chapter->update(['name' => $data['name']]);

        return redirect()
            ->route('teacher.courses.show', $course)
            ->with('success', 'Đã cập nhật tên chương.');
    }

    public function destroy(Course $course, Chapter $chapter): RedirectResponse
    {
        $this->authorizeOwnership($course);

        $chapter->delete();

        return redirect()
            ->route('teacher.courses.show', $course)
            ->with('success', 'Đã xóa chương học và toàn bộ bài học bên trong.');
    }

    private function authorizeOwnership(Course $course): void
    {
        $user = Auth::user();
        abort_unless(
            $user->isAdmin() || $course->teacher_id === (int) $user->getAuthIdentifier(),
            403,
            'Bạn không có quyền thao tác với khóa học này.'
        );
    }
}
