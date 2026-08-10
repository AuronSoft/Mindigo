<?php

namespace Mindigo\StudentClassroom\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\StudentClassroom\Services\ClassroomService;
use Mindigo\TeacherAnnouncement\Models\Announcement;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomAttendanceSession;
use Mindigo\TeacherClassroom\Services\TeacherClassroomService;

class ClassroomController extends Controller
{
    public function __construct(protected ClassroomService $service, private readonly TeacherClassroomService $teacherClassroomService) {}

    public function index(Request $request)
    {
        $classrooms = $this->service->getClassroomsForStudent(Auth::id());

        return view('student-classroom::index', compact('classrooms'));
    }

    public function show(Classroom $classroom)
    {
        // Chặn xem lớp không thuộc về mình
        abort_unless($this->service->isEnrolled($classroom, Auth::id()), 403);

        $detail = $this->service->getClassroomDetail($classroom);

        $attendanceSession = ClassroomAttendanceSession::query()
            ->where('classroom_id', $classroom->id)
            ->where('status', ClassroomAttendanceSession::STATUS_OPEN)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        return view('student-classroom::show', array_merge(['classroom' => $classroom, 'attendanceSession' => $attendanceSession], $detail));
    }

    public function attendanceCheckIn(Request $request, Classroom $classroom)
    {
        abort_unless($this->service->isEnrolled($classroom, Auth::id()), 403);
        $validated = $request->validate(['attendance_code' => ['required', 'alpha_num:ascii', 'size:6']]);

        $this->teacherClassroomService->checkInWithCode($classroom, $request->user(), $validated['attendance_code']);

        return redirect()->route('student.classrooms.show', $classroom)->with('success', __('student-classroom::app.attendance_success'));
    }

    public function announcement(Classroom $classroom, Announcement $announcement)
    {
        // Chỉ xem được thông báo đã phát hành và thuộc về lớp trong URL
        abort_unless(
            $announcement->isPublished()
                && $announcement->classrooms()->whereKey($classroom->id)->exists(),
            404
        );

        // Học sinh phải thuộc lớp trong URL mới xem tại lớp này; ngược lại
        // điều hướng tới lớp đầu tiên (trong các lớp announcement nhắm tới) mà họ tham gia.
        if (! $this->service->isEnrolled($classroom, Auth::id())) {
            $owned = $this->service->classroomForAnnouncement($announcement, Auth::id());

            abort_unless($owned !== null, 403);

            return redirect()->route('student.classrooms.announcements.show', [$owned, $announcement]);
        }

        return view('student-classroom::announcement', compact('classroom', 'announcement'));
    }
}
