<?php

namespace Mindigo\StudentProgress\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\StudentProgress\Services\ProgressService;

class ProgressController extends Controller
{
    public function __construct(protected ProgressService $service)
    {
    }

    public function index(Request $request)
    {
        /** @var \Mindigo\Auth\Models\User $student */
        $student = Auth::user();

        $classroomId = $request->filled('classroom_id') ? (int) $request->input('classroom_id') : null;

        $progress   = $this->service->computeForStudent($student, $classroomId);
        $classrooms = $this->service->getClassroomsForStudent($student->id);

        return view('student-progress::index', compact('progress', 'classrooms', 'classroomId'));
    }
}
