<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionMessage;
use Mindigo\TeacherLiveSession\Models\LiveSessionParticipant;
use Mindigo\TeacherLiveSession\Models\LiveSessionRoomEvent;
use Mindigo\TeacherLiveSession\Services\LiveSessionAccessService;
use Mindigo\TeacherLiveSession\Services\LiveSessionAdmissionService;
use Mindigo\TeacherLiveSession\Services\LiveSessionJoinTokenService;

final class LiveSessionCollaborationController extends Controller
{
    private const REACTIONS = ['clap', 'heart', 'celebrate', 'question'];

    public function __construct(
        private readonly LiveSessionJoinTokenService $tokens,
        private readonly LiveSessionAccessService $access,
        private readonly LiveSessionAdmissionService $admissions,
        private readonly AuditLogService $audit,
    ) {}

    public function sync(Request $request, LiveSession $liveSession): JsonResponse
    {
        $participant = $this->participant($request, $liveSession);
        $data = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
            'after_message_id' => ['nullable', 'integer', 'min:0'],
            'after_event_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $messages = LiveSessionMessage::query()->where('live_session_id', $liveSession->id)
            ->where('id', '>', $data['after_message_id'] ?? 0)->with('sender:id,name')->oldest()->limit(100)->get()
            ->map(fn (LiveSessionMessage $message) => [
                'id' => $message->id, 'sender_id' => $message->sender_id,
                'sender_name' => $message->sender?->name, 'body' => $message->body,
                'sent_at' => $message->created_at?->toIso8601String(),
            ]);
        $events = LiveSessionRoomEvent::query()->where('live_session_id', $liveSession->id)
            ->where('id', '>', $data['after_event_id'] ?? 0)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->where(fn ($query) => $query->whereNull('target_user_id')->orWhere('target_user_id', $request->user()->id))
            ->oldest()->limit(100)->get(['id', 'actor_id', 'target_user_id', 'type', 'payload']);

        return response()->json([
            'messages' => $messages,
            'events' => $events,
            'participants' => $this->participants($liveSession),
            'can_moderate' => $this->access->canModerate($liveSession, $request->user()),
            'self' => [
                'hand_raised' => $participant->hand_raised_at !== null,
                'force_muted' => $participant->force_muted_at !== null,
            ],
        ]);
    }

    public function message(Request $request, LiveSession $liveSession): JsonResponse
    {
        $this->participant($request, $liveSession);
        abort_if(($liveSession->room_settings['chat_enabled'] ?? true) === false, 403);
        $data = $request->validate(['token' => ['required', 'string', 'max:4096'], 'body' => ['required', 'string', 'max:2000']]);
        $body = trim($data['body']);
        if ($body === '') {
            throw ValidationException::withMessages(['body' => __('validation.required', ['attribute' => 'body'])]);
        }
        $message = LiveSessionMessage::query()->create([
            'live_session_id' => $liveSession->id,
            'sender_id' => $request->user()->id,
            'body' => $body,
        ]);

        return response()->json(['id' => $message->id], 201);
    }

    public function action(Request $request, LiveSession $liveSession): JsonResponse
    {
        $participant = $this->participant($request, $liveSession);
        $data = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
            'action' => ['required', Rule::in(['raise_hand', 'lower_hand', 'reaction'])],
            'reaction' => ['required_if:action,reaction', Rule::in(self::REACTIONS)],
        ]);
        if ($data['action'] === 'raise_hand') {
            $participant->update(['hand_raised_at' => $participant->hand_raised_at ?? now()]);
        } elseif ($data['action'] === 'lower_hand') {
            $participant->update(['hand_raised_at' => null]);
        } else {
            LiveSessionRoomEvent::query()->create([
                'live_session_id' => $liveSession->id, 'actor_id' => $request->user()->id,
                'type' => 'reaction', 'payload' => ['reaction' => $data['reaction'], 'name' => $request->user()->name],
                'expires_at' => now()->addSeconds(15),
            ]);
        }

        return response()->json([], 202);
    }

    public function moderate(Request $request, LiveSession $liveSession): JsonResponse
    {
        $this->participant($request, $liveSession);
        abort_unless($this->access->canModerate($liveSession, $request->user()), 403);
        $data = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
            'target_user_id' => ['required', 'integer'],
            'action' => ['required', Rule::in(['mute', 'allow_microphone', 'lower_hand', 'remove'])],
        ]);
        $target = $liveSession->participants()->where('user_id', $data['target_user_id'])->firstOrFail();
        abort_if($target->role->canModerate(), 422);

        if ($data['action'] === 'remove') {
            $this->admissions->remove($liveSession, $target, $request->user());
        } else {
            $attributes = match ($data['action']) {
                'mute' => ['force_muted_at' => now(), 'microphone_enabled' => false],
                'allow_microphone' => ['force_muted_at' => null],
                default => ['hand_raised_at' => null],
            };
            $target->update($attributes);
            LiveSessionRoomEvent::query()->create([
                'live_session_id' => $liveSession->id, 'actor_id' => $request->user()->id,
                'target_user_id' => $target->user_id, 'type' => $data['action'], 'expires_at' => now()->addMinute(),
            ]);
            $this->audit->record('participant_'.$data['action'], 'teacher_live_session', metadata: ['participant_user_id' => $target->user_id], auditable: $liveSession, user: $request->user());
        }

        return response()->json([], 202);
    }

    private function participant(Request $request, LiveSession $session): LiveSessionParticipant
    {
        abort_unless($session->isLive(), 409);
        $this->tokens->validate((string) $request->input('token'), $session, $request->user());

        return $session->participants()->where('user_id', $request->user()->id)
            ->where('admission_status', ParticipantAdmissionStatus::Admitted->value)->firstOrFail();
    }

    private function participants(LiveSession $session): array
    {
        return $session->participants()->where('admission_status', ParticipantAdmissionStatus::Admitted->value)
            ->where('last_seen_at', '>=', now()->subSeconds(20))->with('user:id,name')->orderByDesc('hand_raised_at')->get()
            ->map(fn (LiveSessionParticipant $item) => [
                'user_id' => $item->user_id, 'name' => $item->user?->name, 'role' => $item->role->value,
                'microphone_enabled' => $item->microphone_enabled, 'camera_enabled' => $item->camera_enabled,
                'screen_sharing' => $item->screen_sharing, 'hand_raised' => $item->hand_raised_at !== null,
                'force_muted' => $item->force_muted_at !== null,
            ])->all();
    }
}
