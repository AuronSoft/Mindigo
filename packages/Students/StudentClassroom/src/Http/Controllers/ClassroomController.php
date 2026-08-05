<?php

namespace Mindigo\StudentClassroom\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\StudentClassroom\Services\ClassroomService;

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
}
