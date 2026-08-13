<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use App\Support\LiveSession\ExternalMeetingUrlPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Http\Requests\CancelLiveSessionRequest;
use Mindigo\TeacherLiveSession\Http\Requests\CreateGuestLinkRequest;
use Mindigo\TeacherLiveSession\Http\Requests\LiveSessionRequest;
use Mindigo\TeacherLiveSession\Models\LiveProviderConnection;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionGuest;
use Mindigo\TeacherLiveSession\Models\LiveSessionGuestLink;
use Mindigo\TeacherLiveSession\Models\LiveSessionParticipant;
use Mindigo\TeacherLiveSession\Services\LiveMeetingProviderRegistry;
use Mindigo\TeacherLiveSession\Services\LiveProviderFallbackService;
use Mindigo\TeacherLiveSession\Services\LiveProviderOAuthService;
use Mindigo\TeacherLiveSession\Services\LiveSessionAccessService;
use Mindigo\TeacherLiveSession\Services\LiveSessionAdmissionService;
use Mindigo\TeacherLiveSession\Services\LiveSessionConfigurationService;
use Mindigo\TeacherLiveSession\Services\LiveSessionGuestService;
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
        protected LiveSessionGuestService $guests,
        protected LiveSessionConfigurationService $configuration,
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
        $providerConnections = $this->providerConnections();

        return view('teacher-live-session::create', compact('classrooms', 'providerCapabilities', 'providerConnections'));
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
        $providerConnections = $this->providerConnections();

        return view('teacher-live-session::edit', ['session' => $liveSession, ...compact('classrooms', 'providerCapabilities', 'providerConnections')]);
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

    public function fallbackToNative(Request $request, LiveSession $liveSession, LiveProviderFallbackService $fallback)
    {
        $this->authorizeOwner($liveSession);
        $fallback->switchToNative($liveSession, $request->user());

        return back()->with('success', __('teacher-live-session::app.fallback_native_success'));
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
        $management = $this->roomManagementData($liveSession, $request);
        if ($liveSession->isWaiting() && $liveSession->provider === LiveSessionProvider::Native) {
            return view('teacher-live-session::room', $management + compact('participant') + [
                'session' => $liveSession,
                'mediaConfig' => null,
            ]);
        }
        if (($liveSession->room_settings['recording_enabled'] ?? false) === true && $participant->recording_consented_at === null) {
            return view('teacher-live-session::recording-consent', ['session' => $liveSession]);
        }

        $join = $this->service->join($liveSession, $request->user());
        if (($join['mode'] ?? null) === 'redirect') {
            abort_unless(app(ExternalMeetingUrlPolicy::class)->allows($liveSession->provider, $join['url'] ?? null), 502);

            return redirect()->away($join['url']);
        }
        $join['access_token'] = $this->tokens->issue($liveSession, $request->user(), $role);

        return view('teacher-live-session::room', $management + compact('join', 'participant') + [
            'session' => $liveSession,
            'mediaConfig' => $this->mediaConfig($liveSession, $join['access_token'], $request),
        ]);
    }

    private function roomManagementData(LiveSession $liveSession, Request $request): array
    {
        $waitingParticipants = $liveSession->participants()
            ->where('admission_status', ParticipantAdmissionStatus::Waiting->value)
            ->with('user:id,name,email')
            ->oldest()
            ->get();
        $waitingGuests = LiveSessionGuest::query()->where('live_session_id', $liveSession->id)
            ->where('admission_status', ParticipantAdmissionStatus::Waiting->value)->oldest()->get();
        $admittedGuests = LiveSessionGuest::query()->where('live_session_id', $liveSession->id)
            ->where('admission_status', ParticipantAdmissionStatus::Admitted->value)->oldest()->get();
        $guestLinks = LiveSessionGuestLink::query()->where('live_session_id', $liveSession->id)
            ->whereNull('revoked_at')->where('expires_at', '>', now())->latest()->get();
        $canManageGuestLinks = $this->access->canManage($liveSession, $request->user());

        return compact('waitingParticipants', 'waitingGuests', 'admittedGuests', 'guestLinks', 'canManageGuestLinks');
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

    public function createGuestLink(CreateGuestLinkRequest $request, LiveSession $liveSession)
    {
        $this->authorizeOwner($liveSession);
        $result = $this->guests->createLink($liveSession, $request->user(), (int) $request->validated('ttl_minutes'), $request->validated('max_uses'));

        return back()->with('success', __('teacher-live-session::app.guest_link_created'))->with('guest_link_url', $result['url']);
    }

    public function revokeGuestLink(Request $request, LiveSession $liveSession, LiveSessionGuestLink $guestLink)
    {
        $this->authorizeOwner($liveSession);
        abort_unless((int) $guestLink->live_session_id === (int) $liveSession->id, 404);
        $this->guests->revokeLink($guestLink, $request->user());

        return back()->with('success', __('teacher-live-session::app.guest_link_revoked'));
    }

    public function decideGuest(Request $request, LiveSession $liveSession, LiveSessionGuest $guest)
    {
        $this->authorizeModerator($liveSession, $request);
        abort_unless((int) $guest->live_session_id === (int) $liveSession->id, 404);
        $data = $request->validate(['decision' => ['required', Rule::in(['admitted', 'denied', 'removed'])]]);
        $this->guests->decide($guest, $request->user(), ParticipantAdmissionStatus::from($data['decision']));

        return back()->with('success', __('teacher-live-session::app.guest_decision_saved'));
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

    private function providerConnections(): array
    {
        $oauth = app(LiveProviderOAuthService::class);
        $connections = LiveProviderConnection::query()->where('user_id', Auth::id())->whereNull('revoked_at')
            ->get()->keyBy(fn ($item) => $item->provider->value);

        return collect([LiveSessionProvider::GoogleMeet, LiveSessionProvider::Zoom])->mapWithKeys(
            fn (LiveSessionProvider $provider) => [$provider->value => [
                'configured' => $oauth->isConfigured($provider),
                'connected' => $connections->has($provider->value),
                'account' => $connections->get($provider->value)?->external_email,
            ]]
        )->all();
    }

    private function mediaConfig(LiveSession $session, string $token, Request $request): array
    {
        return [
            'userId' => (int) $request->user()->id,
            'participantKey' => 'user:'.$request->user()->id,
            'participantRole' => $this->access->roleFor($session, $request->user())->value,
            'connectionId' => (string) Str::uuid(),
            'token' => $token,
            'presenceUrl' => route('live-media.presence', $session),
            'signalUrl' => route('live-media.signals.store', $session),
            'inboxUrl' => route('live-media.signals.inbox', $session),
            'mediaLeaveUrl' => route('live-media.leave', $session),
            'collaborationSyncUrl' => route('live-collaboration.sync', $session),
            'messageUrl' => route('live-collaboration.messages.store', $session),
            'actionUrl' => route('live-collaboration.actions.store', $session),
            'moderateUrl' => route('live-collaboration.moderate', $session),
            'leaveUrl' => route('teacher.live-sessions.index'),
            'joinTokenUrl' => route('teacher.live-sessions.join-token', $session),
            'topology' => config('live-media.topology', 'mesh'),
            'gatewayTicketUrl' => route('live-media.gateway-ticket', $session),
            'iceServersUrl' => route('live-media.ice-servers', $session),
            'recordingEnabled' => ($session->room_settings['recording_enabled'] ?? false) === true,
            'canRecord' => $this->access->canModerate($session, $request->user()),
            'recordingStartUrl' => route('live-recordings.start', $session),
            'recordingChunkUrl' => route('live-recordings.chunk', [$session, '__RECORDING__']),
            'recordingFinalizeUrl' => route('live-recordings.finalize', [$session, '__RECORDING__']),
            'recordingStopUrl' => route('live-recordings.stop', [$session, '__RECORDING__']),
            'recordingStatusUrl' => route('live-recordings.status', '__RECORDING__'),
            'recordingAbortUrl' => route('live-recordings.abort', [$session, '__RECORDING__']),
            'teachingToolsSyncUrl' => route('live-teaching-tools.sync', $session),
            'whiteboardUrl' => route('live-teaching-tools.whiteboard', $session),
            'pollCreateUrl' => route('live-teaching-tools.polls.store', $session),
            'pollVoteUrl' => route('live-teaching-tools.polls.vote', [$session, '__POLL__']),
            'pollCloseUrl' => route('live-teaching-tools.polls.close', [$session, '__POLL__']),
            'resourceUploadUrl' => route('live-teaching-tools.resources.store', $session),
            'breakoutSyncUrl' => route('live-breakouts.sync', $session),
            'breakoutCreateUrl' => route('live-breakouts.store', $session),
            'breakoutOpenUrl' => route('live-breakouts.open', $session),
            'breakoutCloseUrl' => route('live-breakouts.close', $session),
            'breakoutAssignUrl' => route('live-breakouts.assign', [$session, '__ROOM__']),
            'breakoutVisitUrl' => route('live-breakouts.visit', [$session, '__ROOM__']),
            'breakoutMainUrl' => route('live-breakouts.main', $session),
            'iceServers' => config('live-media.static_ice_servers', []),
            'maxBitrateKbps' => (int) $this->configuration->value('live_max_bitrate_kbps'),
        ];
    }
}
