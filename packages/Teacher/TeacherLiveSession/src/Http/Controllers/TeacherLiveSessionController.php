<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Http\Requests\CancelLiveSessionRequest;
use Mindigo\TeacherLiveSession\Http\Requests\LiveSessionRequest;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionParticipant;
use Mindigo\TeacherLiveSession\Services\LiveMeetingProviderRegistry;
use Mindigo\TeacherLiveSession\Services\LiveSessionAccessService;
use Mindigo\TeacherLiveSession\Services\LiveSessionAdmissionService;
use Mindigo\TeacherLiveSession\Services\LiveSessionJoinTokenService;
use Mindigo\TeacherLiveSession\Services\LiveSessionLifecycleService;
use Mindigo\TeacherLiveSession\Services\LiveSessionService;

class TeacherLiveSessionController extends Controller
{
    public function __construct(
        protected LiveSessionService $service,
        protected LiveMeetingProviderRegistry $providers,
        protected LiveSessionAccessService $access,
        protected LiveSessionAdmissionService $admissions,
        protected LiveSessionJoinTokenService $tokens,
        protected LiveSessionLifecycleService $lifecycle,
    ) {}

    public function index(Request $request)
    {
        $classroomId = $request->input('classroom_id');

        $sessions = $this->service->getSessionsByTeacher(Auth::id(), $classroomId);
        $classrooms = $this->classroomsForTeacher(includeAssisted: true);

        return view('teacher-live-session::index', compact('sessions', 'classrooms'));
    }

    public function create()
    {
        $classrooms = $this->classroomsForTeacher(withAcademicContext: true);
        $providerCapabilities = $this->providers->capabilities();

        return view('teacher-live-session::create', compact('classrooms', 'providerCapabilities'));
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
        abort_unless(in_array($liveSession->status, ['draft', 'scheduled'], true), 422);

        $classrooms = $this->classroomsForTeacher($liveSession, true);
        $providerCapabilities = $this->providers->capabilities();

        return view('teacher-live-session::edit', ['session' => $liveSession, ...compact('classrooms', 'providerCapabilities')]);
    }

    public function update(LiveSessionRequest $request, LiveSession $liveSession)
    {
        $this->authorizeOwner($liveSession);
        abort_unless(in_array($liveSession->status, ['draft', 'scheduled'], true), 422);
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
        $this->authorizeModerator($liveSession, $request);
        $this->lifecycle->assertCanStart($liveSession);
        abort_unless($this->access->canEnter($liveSession, $request->user()), 403);
        $this->lifecycle->start($liveSession, $request->user());

        return redirect()->route('teacher.live-sessions.room', $liveSession);
    }

    // Phòng học theo provider đã được đăng ký.

    public function room(Request $request, LiveSession $liveSession)
    {
        abort_unless($this->access->canEnter($liveSession, $request->user()), 403);
        $role = $this->access->roleFor($liveSession, $request->user());
        $participant = $this->admissions->requestEntry($liveSession, $request->user(), $role);
        abort_unless($participant->admission_status === ParticipantAdmissionStatus::Admitted, 403);

        $join = $this->service->join($liveSession, $request->user());
        $join['access_token'] = $this->tokens->issue($liveSession, $request->user(), $role);
        $waitingParticipants = $liveSession->participants()
            ->where('admission_status', ParticipantAdmissionStatus::Waiting->value)
            ->with('user:id,name,email')
            ->oldest()
            ->get();

        return view('teacher-live-session::room', compact('liveSession', 'join', 'participant', 'waitingParticipants') + ['session' => $liveSession]);
    }

    // Kết thúc buổi học

    public function end(Request $request, LiveSession $liveSession)
    {
        $this->authorizeModerator($liveSession, $request);
        $this->lifecycle->end($liveSession, $request->user());

        return redirect()
            ->route('teacher.live-sessions.index')
            ->with('success', __('teacher-live-session::app.ended_success'));
    }

    public function openWaitingRoom(Request $request, LiveSession $liveSession)
    {
        $this->authorizeModerator($liveSession, $request);
        abort_unless($this->access->canEnter($liveSession, $request->user()), 403);
        $this->lifecycle->openWaitingRoom($liveSession, $request->user());

        return back()->with('success', __('teacher-live-session::app.waiting_room_opened'));
    }

    public function cancel(CancelLiveSessionRequest $request, LiveSession $liveSession)
    {
        $this->authorizeOwner($liveSession);
        $this->lifecycle->cancel($liveSession, $request->user(), $request->validated('reason'));

        return redirect()->route('teacher.live-sessions.index')->with('success', __('teacher-live-session::app.cancelled_success'));
    }

    public function lock(Request $request, LiveSession $liveSession)
    {
        $this->authorizeModerator($liveSession, $request);
        $this->lifecycle->setLocked($liveSession, $request->user(), true);

        return back()->with('success', __('teacher-live-session::app.room_locked'));
    }

    public function unlock(Request $request, LiveSession $liveSession)
    {
        $this->authorizeModerator($liveSession, $request);
        $this->lifecycle->setLocked($liveSession, $request->user(), false);

        return back()->with('success', __('teacher-live-session::app.room_unlocked'));
    }

    public function admit(Request $request, LiveSession $liveSession, LiveSessionParticipant $participant)
    {
        $this->authorizeModerator($liveSession, $request);
        $this->admissions->admit($liveSession, $participant, $request->user());

        return back()->with('success', __('teacher-live-session::app.participant_admitted'));
    }

    public function deny(Request $request, LiveSession $liveSession, LiveSessionParticipant $participant)
    {
        $this->authorizeModerator($liveSession, $request);
        $this->admissions->deny($liveSession, $participant, $request->user());

        return back()->with('success', __('teacher-live-session::app.participant_denied'));
    }

    public function remove(Request $request, LiveSession $liveSession, LiveSessionParticipant $participant)
    {
        $this->authorizeModerator($liveSession, $request);
        $this->admissions->remove($liveSession, $participant, $request->user());

        return back()->with('success', __('teacher-live-session::app.participant_removed'));
    }

    public function joinToken(Request $request, LiveSession $liveSession): JsonResponse
    {
        abort_unless($this->access->canEnter($liveSession, $request->user()), 403);
        $role = $this->access->roleFor($liveSession, $request->user());
        $participant = $liveSession->participants()->where('user_id', $request->user()->id)->first();
        abort_unless($participant?->admission_status === ParticipantAdmissionStatus::Admitted, 403);

        return response()->json(['token' => $this->tokens->issue($liveSession, $request->user(), $role), 'expires_in' => 600]);
    }

    private function authorizeOwner(LiveSession $session): void
    {
        abort_unless($this->access->canManage($session, request()->user()), 403);
    }

    private function authorizeModerator(LiveSession $session, Request $request): void
    {
        abort_unless($this->access->canModerate($session, $request->user()), 403);
    }

    private function classroomsForTeacher(
        ?LiveSession $current = null,
        bool $withAcademicContext = false,
        bool $includeAssisted = false,
    ) {
        $linkedScheduleIds = LiveSession::query()
            ->when($current, fn ($query) => $query->whereKeyNot($current->id))
            ->whereNotNull('classroom_schedule_id')
            ->select('classroom_schedule_id');

        return Classroom::query()
            ->where(function ($query) use ($includeAssisted): void {
                $query->where('teacher_id', Auth::id());
                if ($includeAssisted) {
                    $query->orWhere('assistant_id', Auth::id());
                }
            })
            ->where('status', 'active')
            ->when($withAcademicContext, fn ($query) => $query->with([
                'course:id,name,starts_at,ends_at,schedule_days,study_time',
                'schedules' => fn ($query) => $query
                    ->whereIn('status', ['draft', 'scheduled'])
                    ->whereNotIn('id', $linkedScheduleIds)
                    ->with('lesson:id,name')
                    ->orderBy('session_date')
                    ->orderBy('start_time'),
            ]))
            ->withCount('students')
            ->orderBy('name')
            ->get();
    }
}
