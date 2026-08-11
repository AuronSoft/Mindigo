<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Services\LiveSessionAccessService;
use Mindigo\TeacherLiveSession\Services\LiveSessionAdmissionService;

final class LiveSessionRecordingConsentController extends Controller
{
    public function __construct(private readonly LiveSessionAccessService $access, private readonly LiveSessionAdmissionService $admissions) {}

    public function store(Request $request, LiveSession $liveSession)
    {
        abort_unless(($liveSession->room_settings['recording_enabled'] ?? false) === true && $this->access->canEnter($liveSession, $request->user()), 403);
        $participant = $this->admissions->requestEntry($liveSession, $request->user(), $this->access->roleFor($liveSession, $request->user()));
        abort_unless($participant->admission_status === ParticipantAdmissionStatus::Admitted, 403);
        $request->validate(['consent' => ['accepted']]);
        $participant->update(['recording_consented_at' => now()]);

        return redirect($request->user()->isStudent() ? route('student.live-sessions.room', $liveSession) : route('teacher.live-sessions.room', $liveSession));
    }
}
