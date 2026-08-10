<?php

namespace Mindigo\AcademicCalendar\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\AcademicCalendar\Http\Requests\AdminCalendarExceptionRequest;
use Mindigo\AcademicCalendar\Models\AcademicCalendarException;
use Mindigo\AcademicCalendar\Services\CalendarExceptionService;
use Mindigo\TeacherCourse\Models\Course;

class AdminCalendarExceptionController extends Controller
{
    public function __construct(private readonly CalendarExceptionService $exceptions) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'scope' => ['nullable', 'in:global,course'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        return view('academic-calendar::admin.exceptions.index', [
            'exceptions' => $this->exceptions->paginate($filters),
            'courses' => Course::query()->select(['id', 'name'])->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function store(AdminCalendarExceptionRequest $request): RedirectResponse
    {
        $this->exceptions->store($request->user(), $request->validated());

        return to_route('admin.calendar-exceptions.index')->with('success', __('academic-calendar::app.exception_saved'));
    }

    public function destroy(AcademicCalendarException $exception): RedirectResponse
    {
        $this->exceptions->delete($exception);

        return back()->with('success', __('academic-calendar::app.exception_deleted'));
    }
}
