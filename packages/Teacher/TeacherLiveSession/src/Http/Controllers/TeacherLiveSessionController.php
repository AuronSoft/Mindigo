<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Http\Requests\LiveSessionRequest;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Services\LiveSessionService;

class TeacherLiveSessionController extends Controller
{
    public function __construct(protected LiveSessionService $service) {}

    public function index(Request $request)
    {
        $classroomId = $request->input('classroom_id');

        $sessions = $this->service->getSessionsByTeacher(Auth::id(), $classroomId);
        $classrooms = $this->classroomsForTeacher();

        return view('teacher-live-session::index', compact('sessions', 'classrooms'));
    }

    public function create()
    {
        $classrooms = $this->classroomsForTeacher();

        return view('teacher-live-session::create', compact('classrooms'));
    }

    public function store(LiveSessionRequest $request)
    {
        $this->service->create($request->validated(), $request->user());

        return redirect()
            ->route('teacher.live-sessions.index')
            ->with('success', __('teacher-live-session::app.created_success'));
    }

    public function edit(LiveSession $liveSession)
    {
        $this->authorizeOwner($liveSession);

        $classrooms = $this->classroomsForTeacher();

        return view('teacher-live-session::edit', ['session' => $liveSession, 'classrooms' => $classrooms]);
    }

    public function update(LiveSessionRequest $request, LiveSession $liveSession)
    {
        $this->authorizeOwner($liveSession);
        $this->service->update($liveSession, $request->validated());

        return redirect()
            ->route('teacher.live-sessions.index')
            ->with('success', __('teacher-live-session::app.updated_success'));
    }

    public function destroy(LiveSession $liveSession)
    {
        $this->authorizeOwner($liveSession);
        $this->service->delete($liveSession);

        return redirect()
            ->route('teacher.live-sessions.index')
            ->with('success', __('teacher-live-session::app.deleted_success'));
    }

    // Bắt đầu buổi học rồi vào phòng

    public function start(Request $request, LiveSession $liveSession)
    {
        $this->authorizeOwner($liveSession);
        $this->service->start($liveSession, $request->user());

        return redirect()->route('teacher.live-sessions.room', $liveSession);
    }

    // Phòng họp (nhúng Jitsi)

    public function room(Request $request, LiveSession $liveSession)
    {
        $this->authorizeOwner($liveSession);
        abort_unless($liveSession->canJoin(), 403);

        $join = $this->service->join($liveSession, $request->user());

        return view('teacher-live-session::room', ['session' => $liveSession, 'join' => $join]);
    }

    // Kết thúc buổi học

    public function end(Request $request, LiveSession $liveSession)
    {
        $this->authorizeOwner($liveSession);
        $this->service->end($liveSession, $request->user());

        return redirect()
            ->route('teacher.live-sessions.index')
            ->with('success', __('teacher-live-session::app.ended_success'));
    }

    private function authorizeOwner(LiveSession $session): void
    {
        abort_if($session->teacher_id !== Auth::id(), 403);
    }

    private function classroomsForTeacher()
    {
        return Classroom::query()
            ->where('teacher_id', Auth::id())
            ->where('status', 'active')
            ->withCount('students')
            ->orderBy('name')
            ->get();
    }
}
