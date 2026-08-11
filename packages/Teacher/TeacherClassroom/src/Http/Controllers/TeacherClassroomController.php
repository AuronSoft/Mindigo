<?php

namespace Mindigo\TeacherClassroom\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\AcademicCalendar\Models\AcademicCalendarException;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Http\Requests\TeacherClassroomRequest;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Services\AttendanceReportService;
use Mindigo\TeacherClassroom\Services\TeacherClassroomService;

class TeacherClassroomController extends Controller
{
    public function __construct(
        private readonly TeacherClassroomService $service,
        private readonly AttendanceReportService $attendanceReports,
    ) {}

    public function index(Request $request)
    {
        session()->forget('url.intended');

        $filters = $request->only(['keyword', 'status']);

        return view('teacher-classroom::index', [
            'classrooms' => $this->service->ownedList($request->user(), $filters),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return view('teacher-classroom::create', $this->service->formData());
    }

    public function store(TeacherClassroomRequest $request): RedirectResponse
    {
        $classroom = $this->service->create($request->user(), $request->classroomData());

        return redirect()
            ->route('teacher.classrooms.show', $classroom)
            ->with('success', __('teacher-classroom::app.created'));
    }

    public function show(Classroom $classroom)
    {
        $this->authorizeOwnership($classroom);

        $classroom->load(['students:id,name,email', 'subjects:id,name,color', 'teacher:id,name,email', 'assistant:id,name,email', 'course:id,name,subject_id,starts_at,ends_at,schedule_days,study_time,duration_value,duration_unit', 'course.chapters.lessons']);

        $formData = $this->service->formData();

        $selectedDate = request('attendance_date', now()->toDateString());
        $attendanceSchedule = $classroom->schedules()->whereKey(request('attendance_schedule_id'))->first();
        $attendanceRecords = $attendanceSchedule ? $this->service->getAttendanceBySchedule($attendanceSchedule) : $this->service->getAttendanceByDate($classroom, $selectedDate);
        $attendanceHistory = $this->service->getAttendanceHistory($classroom);

        $schedules = $classroom->schedules()->with('substituteTeacher:id,name,email')->orderBy('session_date', 'desc')->orderBy('start_time', 'desc')->get();
        $announcements = $classroom->announcements()->latest('published_at')->get();
        $calendarExceptions = AcademicCalendarException::query()->where('classroom_id', $classroom->id)->orderBy('exception_date')->get();

        return view('teacher-classroom::show', [
            'classroom' => $classroom,
            'allStudents' => $formData['students'],
            'availableTeachers' => $formData['teachers'],
            'selectedDate' => $selectedDate,
            'attendanceRecords' => $attendanceRecords,
            'attendanceHistory' => $attendanceHistory,
            'attendanceSummary' => $this->attendanceReports->summary($classroom),
            'attendanceBySession' => $this->attendanceReports->bySession($classroom),
            'attendanceSchedule' => $attendanceSchedule,
            'attendanceSession' => $attendanceSchedule ? $this->service->attendanceSessionForSchedule($attendanceSchedule) : $this->service->attendanceSession($classroom, $selectedDate),
            'schedules' => $schedules,
            'announcements' => $announcements,
            'calendarExceptions' => $calendarExceptions,
        ]);
    }

    public function edit(Classroom $classroom)
    {
        $this->authorizeOwnership($classroom);

        return view('teacher-classroom::edit', [
            'classroom' => $classroom->load(['subjects:id,name,color', 'assistant:id,name,email']),
            ...$this->service->formData(),
        ]);
    }

    public function update(TeacherClassroomRequest $request, Classroom $classroom): RedirectResponse
    {
        $this->authorizeOwnership($classroom);

        $this->service->update($classroom, $request->classroomData());

        return redirect()
            ->route('teacher.classrooms.show', $classroom)
            ->with('success', __('teacher-classroom::app.updated'));
    }

    public function destroy(Classroom $classroom): RedirectResponse
    {
        $this->authorizeOwnership($classroom);
        $classroom->delete();

        return redirect()
            ->route('teacher.classrooms.index')
            ->with('success', __('teacher-classroom::app.deleted'));
    }

    public function syncStudents(Request $request, Classroom $classroom): RedirectResponse
    {
        $this->authorizeOwnership($classroom);

        $validated = $request->validate([
            'student_ids' => ['array'],
            'student_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $this->service->syncStudents($classroom, $validated['student_ids'] ?? []);

        return redirect()
            ->route('teacher.classrooms.show', [$classroom, 'tab' => 'students'])
            ->with('success', __('teacher-classroom::app.students_updated'));
    }

    /**
     * Chặn giáo viên truy cập lớp của người khác. Admin được phép xem tất cả.
     */
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
