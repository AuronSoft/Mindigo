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
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherClassroom\Services\AttendanceReportService;
use Mindigo\TeacherClassroom\Services\TeacherClassroomService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClassroomAttendanceController extends Controller
{
    public function __construct(private readonly TeacherClassroomService $service) {}

    public function saveAttendance(ClassroomAttendanceRequest $request, Classroom $classroom): RedirectResponse
    {
        $this->authorizeOwnership($classroom);

        $validated = $request->validated();

        if (! empty($validated['classroom_schedule_id'])) {
            $schedule = ClassroomSchedule::query()->whereBelongsTo($classroom)->findOrFail($validated['classroom_schedule_id']);
            $this->service->saveScheduleAttendance($schedule, $validated['records'], $request->user(), $validated['change_reason'] ?? null);
        } else {
            $this->service->saveAttendance($classroom, $validated['attendance_date'], $validated['records'], $request->user(), $validated['change_reason'] ?? null);
        }

        return redirect()
            ->route('teacher.classrooms.show', [$classroom, 'tab' => 'attendance', 'attendance_date' => $validated['attendance_date'], 'attendance_schedule_id' => $validated['classroom_schedule_id'] ?? null])
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
        $request->mergeIfMissing(['late_after_minutes' => 15]);
        $validated = $request->validate([
            'attendance_date' => ['required', 'date_format:Y-m-d'],
            'duration_minutes' => ['required', 'integer', 'in:15,30,45,60,90,120'],
            'late_after_minutes' => ['required', 'integer', 'min:0', 'max:60'],
            'classroom_schedule_id' => ['nullable', 'integer', 'exists:classroom_schedules,id'],
        ]);

        if (! empty($validated['classroom_schedule_id'])) {
            $schedule = ClassroomSchedule::query()->whereBelongsTo($classroom)->findOrFail($validated['classroom_schedule_id']);
            $this->service->openScheduleAttendance($schedule, $request->user(), (int) $validated['duration_minutes'], (int) $validated['late_after_minutes']);
        } else {
            $this->service->openCodeAttendance($classroom, $request->user(), $validated['attendance_date'], (int) $validated['duration_minutes'], (int) $validated['late_after_minutes']);
        }

        return redirect()->route('teacher.classrooms.show', [$classroom, 'tab' => 'attendance', 'attendance_date' => $validated['attendance_date'], 'attendance_schedule_id' => $validated['classroom_schedule_id'] ?? null])
            ->with('success', __('teacher-classroom::app.attendance_code_opened'));
    }

    public function closeCodeSession(ClassroomAttendanceSession $attendanceSession): RedirectResponse
    {
        $classroom = $attendanceSession->classroom;
        $this->authorizeOwnership($classroom);
        $this->service->closeCodeAttendance($attendanceSession);

        return redirect()->route('teacher.classrooms.show', [$classroom, 'tab' => 'attendance', 'attendance_date' => $attendanceSession->session_date->toDateString(), 'attendance_schedule_id' => $attendanceSession->classroom_schedule_id])
            ->with('success', __('teacher-classroom::app.attendance_code_closed'));
    }

    public function export(Classroom $classroom, AttendanceReportService $reports): StreamedResponse
    {
        $this->authorizeOwnership($classroom);
        $rows = $reports->exportRows($classroom);

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['Ngày', 'Buổi học', 'Học sinh', 'Email', 'Trạng thái', 'Phút đi muộn', 'Lý do vắng', 'Ghi chú', 'Người cập nhật', 'Cập nhật lúc']);
            foreach ($rows as $record) {
                fputcsv($output, [
                    $record->session_date->format('d/m/Y'), $record->schedule?->title, $record->student?->name,
                    $record->student?->email, $record->status, $record->late_minutes, $record->absence_reason,
                    $record->remarks, $record->editor?->name, $record->updated_at?->format('d/m/Y H:i'),
                ]);
            }
            fclose($output);
        }, 'attendance-'.$classroom->code.'-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
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
