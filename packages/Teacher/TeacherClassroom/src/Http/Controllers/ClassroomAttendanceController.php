<?php

namespace Mindigo\TeacherClassroom\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Http\Requests\ClassroomAttendanceRequest;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomAttendanceSession;
use Mindigo\TeacherClassroom\Services\TeacherClassroomService;

class ClassroomAttendanceController extends Controller
{
    public function __construct(private readonly TeacherClassroomService $service) {}

    public function saveAttendance(ClassroomAttendanceRequest $request, Classroom $classroom): RedirectResponse
    {
        $this->authorizeOwnership($classroom);

        $validated = $request->validated();

        $this->service->saveAttendance($classroom, $validated['attendance_date'], $validated['records']);

        return redirect()
            ->route('teacher.classrooms.show', [$classroom, 'tab' => 'attendance', 'attendance_date' => $validated['attendance_date']])
            ->with('success', __('teacher-classroom::app.attendance_saved', ['date' => Carbon::parse($validated['attendance_date'])->format('d/m/Y')]));
    }

    public function getAttendance(Classroom $classroom)
    {
        $this->authorizeOwnership($classroom);
        $date = request('attendance_date', now()->toDateString());
        $records = $this->service->getAttendanceByDate($classroom, $date);

        return response()->json($records);
    }

    public function openCodeSession(Request $request, Classroom $classroom): RedirectResponse
    {
        $this->authorizeOwnership($classroom);
        $validated = $request->validate([
            'attendance_date' => ['required', 'date_format:Y-m-d'],
            'duration_minutes' => ['required', 'integer', 'in:15,30,45,60,90,120'],
        ]);

        $this->service->openCodeAttendance($classroom, $request->user(), $validated['attendance_date'], (int) $validated['duration_minutes']);

        return redirect()->route('teacher.classrooms.show', [$classroom, 'tab' => 'attendance', 'attendance_date' => $validated['attendance_date']])
            ->with('success', __('teacher-classroom::app.attendance_code_opened'));
    }

    public function closeCodeSession(ClassroomAttendanceSession $attendanceSession): RedirectResponse
    {
        $classroom = $attendanceSession->classroom;
        $this->authorizeOwnership($classroom);
        $this->service->closeCodeAttendance($attendanceSession);

        return redirect()->route('teacher.classrooms.show', [$classroom, 'tab' => 'attendance', 'attendance_date' => $attendanceSession->session_date->toDateString()])
            ->with('success', __('teacher-classroom::app.attendance_code_closed'));
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
