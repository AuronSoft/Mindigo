<?php

namespace Mindigo\TeacherClassroom\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\TeacherClassroom\Http\Requests\TeacherClassroomRequest;
use Mindigo\TeacherClassroom\Services\TeacherClassroomService;

class TeacherClassroomController extends Controller
{
    public function __construct(private readonly TeacherClassroomService $service) {}

    public function index(Request $request)
    {
        session()->forget('url.intended');

        $filters = $request->only(['keyword', 'status']);

        return view('teacher-classroom::index', [
            'classrooms' => $this->service->ownedList($request->user(), $filters),
            'stats'      => $this->service->stats($request->user()),
            'filters'    => $filters,
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

        $classroom->load(['students:id,name,email', 'subjects:id,name,color', 'assistant:id,name,email']);

        $formData = $this->service->formData();

        $selectedDate = request('attendance_date', now()->toDateString());
        $attendanceRecords = $this->service->getAttendanceByDate($classroom, $selectedDate);
        $attendanceHistory = $this->service->getAttendanceHistory($classroom);

        $schedules = $classroom->schedules()->orderBy('session_date', 'desc')->orderBy('start_time', 'desc')->get();
        $announcements = $classroom->announcements()->latest('published_at')->get();

        return view('teacher-classroom::show', [
            'classroom'          => $classroom,
            'allStudents'        => $formData['students'],
            'assistants'         => $formData['assistants'],
            'selectedDate'       => $selectedDate,
            'attendanceRecords'  => $attendanceRecords,
            'attendanceHistory'  => $attendanceHistory,
            'schedules'          => $schedules,
            'announcements'      => $announcements,
        ]);
    }

    public function edit(Classroom $classroom)
    {
        $this->authorizeOwnership($classroom);

        return view('teacher-classroom::edit', [
            'classroom' => $classroom,
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
            'student_ids'   => ['array'],
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
        /** @var \Mindigo\Auth\Models\User $user */
        $user = Auth::user();

        abort_unless(
            $user->isAdmin() || $classroom->teacher_id === (int) $user->getAuthIdentifier(),
            403,
            __('teacher-classroom::app.unauthorized')
        );
    }
}
