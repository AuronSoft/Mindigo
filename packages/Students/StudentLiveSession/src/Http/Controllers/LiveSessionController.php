<?php

namespace Mindigo\StudentLiveSession\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentLiveSession\Services\LiveSessionService;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Services\LiveSessionAccessService;
use Mindigo\TeacherLiveSession\Services\LiveSessionAdmissionService;
use Mindigo\TeacherLiveSession\Services\LiveSessionJoinTokenService;

class LiveSessionController extends Controller
{
    public function __construct(
        protected LiveSessionService $service,
        protected LiveSessionAccessService $access,
        protected LiveSessionAdmissionService $admissions,
        protected LiveSessionJoinTokenService $tokens,
    ) {}

    public function index(Request $request)
    {
        $studentId = $request->user()->getAuthIdentifier();

        return view('student-live-session::index', [
            'sessions' => $this->service->getSessionsForStudent($studentId, $request->input('classroom_id')),
            'classrooms' => $this->service->getClassroomsForStudent($studentId),
        ]);
    }

    public function room(Request $request, LiveSession $liveSession)
    {
        abort_unless($this->access->canEnter($liveSession, $request->user()), 403);
        $role = $this->access->roleFor($liveSession, $request->user());
        $participant = $this->admissions->requestEntry($liveSession, $request->user(), $role);

        if (! $liveSession->isLive() || $participant->admission_status !== ParticipantAdmissionStatus::Admitted) {
            return view('student-live-session::waiting', ['session' => $liveSession, 'participant' => $participant]);
        }

        $join = $this->service->join($liveSession, $request->user());
        $join['access_token'] = $this->tokens->issue($liveSession, $request->user(), $role);

        return view('student-live-session::room', ['session' => $liveSession, 'join' => $join]);
    }

    public function joinToken(Request $request, LiveSession $liveSession): JsonResponse
    {
        abort_unless($this->access->canEnter($liveSession, $request->user()), 403);
        $role = $this->access->roleFor($liveSession, $request->user());
        $participant = $liveSession->participants()->where('user_id', $request->user()->id)->first();
        abort_unless($liveSession->isLive() && $participant?->admission_status === ParticipantAdmissionStatus::Admitted, 403);

        return response()->json([
            'token' => $this->tokens->issue($liveSession, $request->user(), $role),
            'expires_in' => 600,
        ]);
    }
}
