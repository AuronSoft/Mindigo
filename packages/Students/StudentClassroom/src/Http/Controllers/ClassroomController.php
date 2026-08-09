<?php

namespace Mindigo\StudentClassroom\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\StudentClassroom\Services\ClassroomService;
use Mindigo\TeacherAnnouncement\Models\Announcement;

class ClassroomController extends Controller
{
    public function __construct(protected ClassroomService $service) {}

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

        return view('student-classroom::show', array_merge(['classroom' => $classroom], $detail));
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
