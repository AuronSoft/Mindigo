<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mindigo\TeacherCourse\Http\Requests\CourseRequest;
use Mindigo\TeacherCourse\Models\Course;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        session()->forget('url.intended');
        $user = Auth::user();

        $query = Course::query()
            ->where('teacher_id', $user->getAuthIdentifier())
            ->withCount(['chapters', 'lessons'])
            ->latest();

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return view('teacher-course::index', [
            'courses' => $query->paginate(12)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return view('teacher-course::create');
    }

    public function store(CourseRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = Auth::user();
        $slug = $this->uniqueSlug($data['name']);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('courses/covers', 'public');
        }

        $course = Course::create([
            'teacher_id'  => $user->getAuthIdentifier(),
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'cover_image' => $coverPath,
            'status'      => $data['status'],
        ]);

        return redirect()
            ->route('teacher.courses.show', $course)
            ->with('success', __('teacher-course::app.course_created'));
    }

    public function show(Course $course)
    {
        $this->authorizeOwnership($course);

        $course->load(['chapters.lessons.assignment:id,title', 'chapters.lessons.prerequisite:id,name']);

        return view('teacher-course::show', compact('course'));
    }

    public function edit(Course $course)
    {
        $this->authorizeOwnership($course);

        return view('teacher-course::edit', compact('course'));
    }

    public function update(CourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorizeOwnership($course);

        $data = $request->validated();

        $slug = $course->name === $data['name'] ? $course->slug : $this->uniqueSlug($data['name'], $course);

        $coverPath = $course->cover_image;
        if ($request->hasFile('cover_image')) {
            if ($course->cover_image) {
                Storage::disk('public')->delete($course->cover_image);
            }
            $coverPath = $request->file('cover_image')->store('courses/covers', 'public');
        }

        $course->update([
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'cover_image' => $coverPath,
            'status'      => $data['status'],
        ]);

        return redirect()
            ->route('teacher.courses.show', $course)
            ->with('success', __('teacher-course::app.course_updated'));
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorizeOwnership($course);
        $course->delete();

        return redirect()
            ->route('teacher.courses.index')
            ->with('success', __('teacher-course::app.course_deleted'));
    }

    private function authorizeOwnership(Course $course): void
    {
        $user = Auth::user();
        abort_unless(
            $user->isAdmin() || $course->teacher_id === (int) $user->getAuthIdentifier(),
            403,
            __('teacher-course::app.unauthorized_course')
        );
    }

    private function uniqueSlug(string $name, ?Course $ignore = null): string
    {
        $base = Str::slug($name) ?: 'khoa-hoc';
        $slug = $base;
        $i = 1;
        while (
            Course::query()
                ->where('slug', $slug)
                ->when($ignore, fn ($q) => $q->whereKeyNot($ignore->getKey()))
                ->withTrashed()
                ->exists()
        ) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
