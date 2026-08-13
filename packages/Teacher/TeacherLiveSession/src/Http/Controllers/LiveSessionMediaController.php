<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Http\Requests\MediaSignalRequest;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionGuest;
use Mindigo\TeacherLiveSession\Models\LiveSessionGuestSignal;
use Mindigo\TeacherLiveSession\Models\LiveSessionParticipant;
use Mindigo\TeacherLiveSession\Models\LiveSessionSignal;
use Mindigo\TeacherLiveSession\Services\LiveMediaGatewayTicketService;
use Mindigo\TeacherLiveSession\Services\LiveSessionAttendanceService;
use Mindigo\TeacherLiveSession\Services\LiveSessionJoinTokenService;
use Mindigo\TeacherLiveSession\Services\TurnCredentialService;

final class LiveSessionMediaController extends Controller
{
    public function __construct(
        private readonly LiveSessionJoinTokenService $tokens,
        private readonly LiveSessionAttendanceService $attendance,
        private readonly LiveMediaGatewayTicketService $gatewayTickets,
        private readonly TurnCredentialService $turnCredentials,
    ) {}

    public function iceServers(Request $request, LiveSession $liveSession): JsonResponse
    {
        $this->participant($request, $liveSession);

        return response()->json($this->turnCredentials->issue(
            (int) $liveSession->id,
            'user:'.$request->user()->id,
        ));
    }

    public function gatewayTicket(Request $request, LiveSession $liveSession): JsonResponse
    {
        abort_unless(config('live-media.topology') === 'sfu', 409);
        $participant = $this->participant($request, $liveSession);
        $ticket = $this->gatewayTickets->issue(
            (int) $liveSession->id,
            'user:'.$request->user()->id,
            $participant->role->value,
            $participant->breakout_room_id,
            $request->user()->name,
        );

        return response()->json($ticket + [
            'gateway_url' => config('live-media.gateway.public_url'),
            'topology' => 'sfu',
        ]);
    }

    public function presence(Request $request, LiveSession $liveSession): JsonResponse
    {
        $participant = $this->participant($request, $liveSession);
        $data = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
            'connection_id' => ['required', 'string', 'max:100'],
            'microphone_enabled' => ['sometimes', 'boolean'],
            'camera_enabled' => ['sometimes', 'boolean'],
            'screen_sharing' => ['sometimes', 'boolean'],
        ]);
        $participant->update([
            'connection_id' => $data['connection_id'],
            'microphone_enabled' => $participant->force_muted_at
                ? false
                : ($data['microphone_enabled'] ?? $participant->microphone_enabled),
            'camera_enabled' => $data['camera_enabled'] ?? $participant->camera_enabled,
            'screen_sharing' => $data['screen_sharing'] ?? $participant->screen_sharing,
            'last_seen_at' => now(),
        ]);
        $this->attendance->heartbeat($liveSession, $request->user(), [
            'microphone_enabled' => $participant->microphone_enabled,
            'camera_enabled' => $participant->camera_enabled,
        ]);

        $participants = $liveSession->participants()
            ->where('admission_status', ParticipantAdmissionStatus::Admitted->value)
            ->where('breakout_room_id', $participant->breakout_room_id)
            ->where('last_seen_at', '>=', now()->subSeconds(20))
            ->with('user:id,name')
            ->get()
            ->map(fn (LiveSessionParticipant $item) => [
                'key' => 'user:'.$item->user_id,
                'user_id' => $item->user_id,
                'name' => $item->user?->name,
                'role' => $item->role->value,
                'microphone_enabled' => $item->microphone_enabled,
                'camera_enabled' => $item->camera_enabled,
                'screen_sharing' => $item->screen_sharing,
            ]);

        $guests = collect();
        if ($participant->breakout_room_id === null) {
            $guests = LiveSessionGuest::query()->where('live_session_id', $liveSession->id)
                ->where('admission_status', ParticipantAdmissionStatus::Admitted->value)
                ->where('last_seen_at', '>=', now()->subSeconds(20))->get()
                ->map(fn (LiveSessionGuest $guest) => [
                    'key' => 'guest:'.$guest->id, 'user_id' => null, 'name' => $guest->name, 'role' => 'guest',
                    'microphone_enabled' => $guest->microphone_enabled, 'camera_enabled' => $guest->camera_enabled,
                    'screen_sharing' => $guest->screen_sharing,
                ]);
        }

        return response()->json(['participants' => $participants->concat($guests)->values()]);
    }

    public function signal(MediaSignalRequest $request, LiveSession $liveSession): JsonResponse
    {
        $sender = $this->participant($request, $liveSession);
        $data = $request->validated();
        $recipientKey = $data['recipient_key'] ?? 'user:'.$data['recipient_id'];
        [$recipientType, $recipientId] = explode(':', $recipientKey, 2);
        $recipientExists = $recipientType === 'user'
            ? $liveSession->participants()->where('user_id', $recipientId)->where('breakout_room_id', $sender->breakout_room_id)->where('admission_status', ParticipantAdmissionStatus::Admitted->value)->exists()
            : $sender->breakout_room_id === null && LiveSessionGuest::query()->where('live_session_id', $liveSession->id)->whereKey($recipientId)->where('admission_status', ParticipantAdmissionStatus::Admitted->value)->exists();
        abort_unless($recipientExists, 422);

        if ($recipientType === 'guest') {
            LiveSessionGuestSignal::query()->create([
                'live_session_id' => $liveSession->id, 'sender_key' => 'user:'.$request->user()->id,
                'recipient_key' => $recipientKey, 'type' => $data['type'], 'payload' => $data['payload'],
            ]);

            return response()->json([], 202);
        }

        LiveSessionSignal::query()->create([
            'live_session_id' => $liveSession->id,
            'sender_id' => $request->user()->id,
            'recipient_id' => (int) $recipientId,
            'type' => $data['type'],
            'payload' => $data['payload'],
        ]);

        return response()->json([], 202);
    }

    public function inbox(Request $request, LiveSession $liveSession): JsonResponse
    {
        $this->participant($request, $liveSession);
        $request->validate(['token' => ['required', 'string', 'max:4096']]);

        $signals = DB::transaction(function () use ($liveSession, $request) {
            $items = LiveSessionSignal::query()->lockForUpdate()
                ->where('live_session_id', $liveSession->id)
                ->where('recipient_id', $request->user()->id)
                ->whereNull('consumed_at')->oldest()->limit(100)->get();
            LiveSessionSignal::query()->whereKey($items->modelKeys())->update(['consumed_at' => now()]);

            $native = $items->map(fn ($item) => [
                'id' => 'u'.$item->id, 'sender_key' => 'user:'.$item->sender_id,
                'sender_id' => $item->sender_id, 'type' => $item->type, 'payload' => $item->payload,
            ]);
            $guestItems = LiveSessionGuestSignal::query()->lockForUpdate()
                ->where('live_session_id', $liveSession->id)->where('recipient_key', 'user:'.$request->user()->id)
                ->whereNull('consumed_at')->oldest()->limit(100)->get();
            LiveSessionGuestSignal::query()->whereKey($guestItems->modelKeys())->update(['consumed_at' => now()]);

            return $native->concat($guestItems->map(fn ($item) => [
                'id' => 'g'.$item->id, 'sender_key' => $item->sender_key,
                'sender_id' => null, 'type' => $item->type, 'payload' => $item->payload,
            ]))->values();
        });

        return response()->json(['signals' => $signals]);
    }

    public function leave(Request $request, LiveSession $liveSession): JsonResponse
    {
        $this->participant($request, $liveSession);
        $request->validate(['token' => ['required', 'string', 'max:4096']]);
        $this->attendance->leave($liveSession, $request->user());

        return response()->json([], 204);
    }

    private function participant(Request $request, LiveSession $session): LiveSessionParticipant
    {
        abort_unless($session->isLive(), 409);
        $this->tokens->validate((string) $request->input('token'), $session, $request->user());

        return $session->participants()->where('user_id', $request->user()->id)
            ->where('admission_status', ParticipantAdmissionStatus::Admitted->value)->firstOrFail();
    }
}
