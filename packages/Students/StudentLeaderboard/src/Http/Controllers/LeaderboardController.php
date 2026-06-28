<?php

namespace Mindigo\StudentLeaderboard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentLeaderboard\Services\LeaderboardService;

class LeaderboardController extends Controller
{
    public function __construct(protected LeaderboardService $service)
    {
    }

    public function index(Request $request)
    {
        $student = $request->user();

        $classrooms = $this->service->getClassroomsForStudent((int) $student->id);

        // Only honor classroom_id if it is one of the student's classrooms.
        $classroomId = $request->integer('classroom_id') ?: null;
        if ($classroomId !== null && ! $classrooms->contains('id', $classroomId)) {
            $classroomId = null;
        }

        $ranking = $this->service->ranking($student, $classroomId);

        return view('student-leaderboard::index', [
            'ranking' => $ranking,
            'classrooms' => $classrooms,
            'classroomId' => $classroomId,
        ]);
    }
}
