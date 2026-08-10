<?php

namespace Mindigo\TeacherClassroom\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\AcademicCalendar\Models\AcademicCalendarException;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Http\Requests\ClassroomScheduleRequest;
use Mindigo\TeacherClassroom\Http\Requests\GenerateCourseScheduleRequest;
use Mindigo\TeacherClassroom\Http\Requests\StoreCalendarExceptionRequest;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherClassroom\Services\TeacherClassroomService;

class ClassroomScheduleController extends Controller
{
    public function __construct(private readonly TeacherClassroomService $service) {}

    public function storeSchedule(ClassroomScheduleRequest $request, Classroom $classroom): RedirectResponse
    {
        $this->authorizeOwnership($classroom);

        $this->service->addSchedule($classroom, $request->validated(), $request->user());

        return redirect()
            ->route('teacher.classrooms.show', [$classroom, 'tab' => 'schedule'])
            ->with('success', __('teacher-classroom::app.schedule_created'));
    }

    public function updateSchedule(ClassroomScheduleRequest $request, ClassroomSchedule $schedule): RedirectResponse
    {
        $classroom = $schedule->classroom;
        $this->authorizeOwnership($classroom);

        $this->service->updateSchedule($schedule, $request->validated(), $request->user());

        return redirect()
            ->route('teacher.classrooms.show', [$classroom, 'tab' => 'schedule'])
            ->with('success', __('teacher-classroom::app.schedule_updated'));
    }

    public function generateCoursePlan(GenerateCourseScheduleRequest $request, Classroom $classroom): RedirectResponse
    {
        $this->authorizeOwnership($classroom);
        $result = $this->service->generateCourseSchedulePlan(
            $classroom,
            $request->user(),
            $request->validated('start_date'),
            (int) $request->validated('session_count'),
        );

        return redirect()->route('teacher.classrooms.show', [$classroom, 'tab' => 'schedule'])
            ->with('success', __('teacher-classroom::app.course_plan_generated', $result));
    }

    public function storeException(StoreCalendarExceptionRequest $request, Classroom $classroom): RedirectResponse
    {
        $this->authorizeOwnership($classroom);
        $this->service->storeCalendarException($classroom, $request->user(), $request->validated());

        return redirect()->route('teacher.classrooms.show', [$classroom, 'tab' => 'schedule'])
            ->with('success', __('teacher-classroom::app.calendar_exception_saved'));
    }

    public function destroyException(AcademicCalendarException $exception): RedirectResponse
    {
        $classroom = $exception->classroom;
        abort_unless($classroom, 404);
        $this->authorizeOwnership($classroom);
        $exception->delete();

        return redirect()->route('teacher.classrooms.show', [$classroom, 'tab' => 'schedule'])
            ->with('success', __('teacher-classroom::app.calendar_exception_deleted'));
    }

    public function destroySchedule(ClassroomSchedule $schedule): RedirectResponse
    {
        $classroom = $schedule->classroom;
        $this->authorizeOwnership($classroom);

        $this->service->deleteSchedule($schedule);

        return redirect()
            ->route('teacher.classrooms.show', [$classroom, 'tab' => 'schedule'])
            ->with('success', __('teacher-classroom::app.schedule_deleted'));
    }

    private function authorizeOwnership(Classroom $classroom): void
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless(
            $user->isAdmin() || $classroom->teacher_id === (int) $user->getAuthIdentifier(),
            403,
            __('teacher-classroom::app.unauthorized')
        );
    }
}
