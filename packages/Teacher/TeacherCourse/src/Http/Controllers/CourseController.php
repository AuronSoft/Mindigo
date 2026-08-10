<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Mindigo\TeacherCourse\Http\Requests\CourseIndexRequest;
use Mindigo\TeacherCourse\Http\Requests\CourseRequest;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Services\CourseEnrollmentService;
use Mindigo\TeacherCourse\Services\CourseService;

class CourseController extends Controller
{
    public function __construct(
        private readonly CourseService $courses,
        private readonly CourseEnrollmentService $enrollments,
    ) {}

    public function index(CourseIndexRequest $request): View
    {
        Gate::authorize('viewAny', Course::class);
        session()->forget('url.intended');
        $filters = $request->safe()->only(['search', 'status', 'publication_status']);

        return view('teacher-course::index', [
            'courses' => $this->courses->ownedList($request->user(), $filters),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Course::class);

        return view('teacher-course::create', $this->courses->formData());
    }

    public function store(CourseRequest $request): RedirectResponse
    {
        $course = $this->courses->create($request->user(), $request->validated(), $request->file('cover_image'));

        return redirect()->route('teacher.courses.show', $course)->with('success', __('teacher-course::app.course_created'));
    }

    public function show(Request $request, Course $course): View
    {
        Gate::authorize('view', $course);

        return view('teacher-course::show', [
            'course' => $this->courses->detail($course),
            'classrooms' => $this->enrollments->teacherClassrooms($request->user(), $course),
        ]);
    }

    public function edit(Course $course): View
    {
        Gate::authorize('update', $course);

        return view('teacher-course::edit', ['course' => $course, ...$this->courses->formData()]);
    }

    public function update(CourseRequest $request, Course $course): RedirectResponse
    {
        $this->courses->update($course, $request->validated(), $request->file('cover_image'));

        return redirect()->route('teacher.courses.show', $course)->with('success', __('teacher-course::app.course_updated'));
    }

    public function destroy(Course $course): RedirectResponse
    {
        Gate::authorize('delete', $course);
        $this->courses->delete($course);

        return redirect()->route('teacher.courses.index')->with('success', __('teacher-course::app.course_deleted'));
    }
}
