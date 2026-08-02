<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Mindigo\TeacherCourse\Http\Requests\CourseCategoryRequest;
use Mindigo\TeacherCourse\Models\CourseCategory;
use Mindigo\TeacherCourse\Services\CourseCategoryService;

class CourseCategoryController extends Controller
{
    public function __construct(private readonly CourseCategoryService $categories) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', CourseCategory::class);
        $filters = $request->only(['search', 'status']);

        return view('teacher-course::categories.index', [
            'categories' => $this->categories->paginated($filters),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', CourseCategory::class);

        return view('teacher-course::categories.form');
    }

    public function store(CourseCategoryRequest $request): RedirectResponse
    {
        $this->categories->create($request->validated());

        return redirect()->route('admin.course-categories.index')->with('success', __('teacher-course::categories.created'));
    }

    public function edit(CourseCategory $courseCategory): View
    {
        Gate::authorize('update', $courseCategory);

        return view('teacher-course::categories.form', ['category' => $courseCategory]);
    }

    public function update(CourseCategoryRequest $request, CourseCategory $courseCategory): RedirectResponse
    {
        $this->categories->update($courseCategory, $request->validated());

        return redirect()->route('admin.course-categories.index')->with('success', __('teacher-course::categories.updated'));
    }

    public function destroy(CourseCategory $courseCategory): RedirectResponse
    {
        Gate::authorize('delete', $courseCategory);
        $this->categories->delete($courseCategory);

        return redirect()->route('admin.course-categories.index')->with('success', __('teacher-course::categories.deleted'));
    }
}
