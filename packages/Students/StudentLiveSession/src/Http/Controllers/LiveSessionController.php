<?php

namespace Mindigo\StudentLiveSession\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentLiveSession\Services\LiveSessionService;
use Mindigo\TeacherLiveSession\Models\LiveSession;

class LiveSessionController extends Controller
{
    public function __construct(protected LiveSessionService $service) {}

    public function index(Request $request)
    {
        $classroomId = $request->input('classroom_id');

        $sessions = $this->service->getSessionsForStudent($request->user()->getAuthIdentifier(), $classroomId);
        $classrooms = $this->service->getClassroomsForStudent($request->user()->getAuthIdentifier());

        return view('student-live-session::index', compact('sessions', 'classrooms'));
    }

    public function room(Request $request, LiveSession $liveSession)
    {
        // Chỉ học sinh thuộc lớp của buổi học mới được vào
        abort_unless($this->service->canAccess($liveSession, $request->user()->getAuthIdentifier()), 403);

        // Chỉ vào được khi buổi học đang diễn ra (giáo viên đã bắt đầu)
        abort_unless($liveSession->isLive(), 403);

        $join = $this->service->join($liveSession, $request->user());

        return view('student-live-session::room', ['session' => $liveSession, 'join' => $join]);
    }
}
