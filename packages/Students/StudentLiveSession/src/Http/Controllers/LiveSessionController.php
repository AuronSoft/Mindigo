<?php

namespace Mindigo\StudentLiveSession\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\StudentLiveSession\Services\LiveSessionService;
use Mindigo\TeacherLiveSession\Models\LiveSession;

class LiveSessionController extends Controller
{
    public function __construct(protected LiveSessionService $service) {}

    public function index(Request $request)
    {
        $classroomId = $request->input('classroom_id');

        $sessions = $this->service->getSessionsForStudent(Auth::id(), $classroomId);
        $classrooms = $this->service->getClassroomsForStudent(Auth::id());

        return view('student-live-session::index', compact('sessions', 'classrooms'));
    }

    public function room(LiveSession $liveSession)
    {
        // Chỉ học sinh thuộc lớp của buổi học mới được vào
        abort_unless($this->service->canAccess($liveSession, Auth::id()), 403);

        // Chỉ vào được khi buổi học đang diễn ra (giáo viên đã bắt đầu)
        abort_unless($liveSession->isLive(), 403);

        $this->service->recordJoin($liveSession, Auth::id());

        return view('student-live-session::room', ['session' => $liveSession]);
    }
}
