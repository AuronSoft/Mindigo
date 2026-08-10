<?php

namespace Mindigo\TeacherCalendar\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherCalendar\Http\Requests\CancelCalendarSessionRequest;
use Mindigo\TeacherCalendar\Http\Requests\CompleteCalendarSessionRequest;
use Mindigo\TeacherCalendar\Http\Requests\OpenCalendarAttendanceRequest;
use Mindigo\TeacherCalendar\Http\Requests\TeacherCalendarIndexRequest;
use Mindigo\TeacherCalendar\Http\Requests\UpdateCalendarSessionRequest;
use Mindigo\TeacherCalendar\Services\TeacherCalendarService;
use Mindigo\TeacherClassroom\Http\Requests\ClassroomScheduleRequest;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherClassroom\Services\TeacherClassroomService;

class TeacherCalendarController extends Controller
{
    public function __construct(private readonly TeacherCalendarService $calendar) {}

    public function index(TeacherCalendarIndexRequest $request): View
    {
        /** @var User $teacher */
        $teacher = $request->user();
        $anchor = CarbonImmutable::createFromFormat('Y-m-d', $request->validated('date') ?? now()->toDateString())
            ->setTimezone(config('app.timezone'));
        $viewMode = $request->validated('view') ?? 'week';
        $period = $this->calendar->period($teacher, $anchor, $viewMode, $request->validated());
        $classrooms = $this->calendar->classrooms($teacher);

        return view('teacher-calendar::index', [
            ...$period,
            'anchor' => $anchor,
            'viewMode' => $viewMode,
            'classrooms' => $classrooms,
            'filters' => $request->validated(),
        ]);
    }

    public function store(
        ClassroomScheduleRequest $request,
        Classroom $classroom,
        TeacherClassroomService $classrooms,
    ): RedirectResponse {
        abort_unless($classroom->teacher_id === (int) $request->user()->getAuthIdentifier(), 403);
        $classrooms->addSchedule($classroom, $request->validated(), $request->user());

        return redirect()->route('teacher.calendar.index', ['date' => $request->string('session_date')->toString()])
            ->with('success', __('teacher-calendar::app.session_created'));
    }

    public function cancel(
        CancelCalendarSessionRequest $request,
        ClassroomSchedule $schedule,
        TeacherClassroomService $classrooms,
    ): RedirectResponse {
        $classrooms->cancelSchedule($schedule, $request->validated('cancel_reason'), $request->user());

        return back()->with('success', __('teacher-calendar::app.session_cancelled'));
    }

    public function update(
        UpdateCalendarSessionRequest $request,
        ClassroomSchedule $schedule,
        TeacherClassroomService $classrooms,
    ): RedirectResponse {
        $classrooms->updateScheduleDetails($schedule, $request->validated(), $request->user());

        return back()->with('success', __('teacher-calendar::app.session_updated'));
    }

    public function reschedule(
        ClassroomScheduleRequest $request,
        ClassroomSchedule $schedule,
        TeacherClassroomService $classrooms,
    ): RedirectResponse {
        abort_unless($schedule->classroom?->teacher_id === (int) $request->user()->getAuthIdentifier(), 403);
        abort_if(in_array($schedule->status, [ClassroomSchedule::STATUS_CANCELLED, ClassroomSchedule::STATUS_RESCHEDULED, ClassroomSchedule::STATUS_COMPLETED], true), 422);

        $replacement = $classrooms->rescheduleSchedule($schedule, $request->validated(), $request->user());

        return redirect()->route('teacher.calendar.index', ['date' => $replacement->session_date->toDateString()])
            ->with('success', __('teacher-calendar::app.session_rescheduled'));
    }

    public function complete(
        CompleteCalendarSessionRequest $request,
        ClassroomSchedule $schedule,
        TeacherClassroomService $classrooms,
    ): RedirectResponse {
        $classrooms->completeSchedule($schedule, $request->user());

        return back()->with('success', __('teacher-calendar::app.session_completed'));
    }

    public function openAttendance(
        OpenCalendarAttendanceRequest $request,
        ClassroomSchedule $schedule,
        TeacherClassroomService $classrooms,
    ): RedirectResponse {
        $classrooms->openScheduleAttendance($schedule, $request->user(), (int) $request->validated('duration_minutes'));

        return back()->with('success', __('teacher-calendar::app.attendance_opened'));
    }
}
