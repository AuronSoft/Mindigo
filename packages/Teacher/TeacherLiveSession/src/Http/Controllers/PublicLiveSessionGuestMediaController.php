<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionGuest;
use Mindigo\TeacherLiveSession\Models\LiveSessionGuestSignal;
use Mindigo\TeacherLiveSession\Models\LiveSessionParticipant;
use Mindigo\TeacherLiveSession\Services\LiveMediaGatewayTicketService;
use Mindigo\TeacherLiveSession\Services\LiveSessionGuestService;

final class PublicLiveSessionGuestMediaController extends Controller
{
    public function __construct(
        private readonly LiveSessionGuestService $guests,
        private readonly LiveMediaGatewayTicketService $gatewayTickets,
    ) {}

    public function gatewayTicket(Request $request, LiveSession $liveSession, LiveSessionGuest $guest): JsonResponse
    {
        abort_unless(config('live-media.topology') === 'sfu', 409);
        $guest = $this->guest($request, $liveSession, $guest);

        return response()->json($this->gatewayTickets->issue(
            (int) $liveSession->id,
            'guest:'.$guest->id,
            'guest',
            null,
            $guest->name,
        ) + [
            'gateway_url' => config('live-media.gateway.public_url'),
            'topology' => 'sfu',
        ]);
    }

    public function presence(Request $request, LiveSession $liveSession, LiveSessionGuest $guest): JsonResponse
    {
        $guest = $this->guest($request, $liveSession, $guest);
        $data = $request->validate([
            'connection_id' => ['required', 'string', 'max:100'], 'microphone_enabled' => ['sometimes', 'boolean'],
            'camera_enabled' => ['sometimes', 'boolean'], 'screen_sharing' => ['sometimes', 'boolean'],
        ]);
        $guest->update([
            'connection_id' => $data['connection_id'], 'microphone_enabled' => $data['microphone_enabled'] ?? $guest->microphone_enabled,
            'camera_enabled' => $data['camera_enabled'] ?? $guest->camera_enabled,
            'screen_sharing' => $data['screen_sharing'] ?? $guest->screen_sharing, 'last_seen_at' => now(),
        ]);

        return response()->json(['participants' => $this->participants($liveSession)]);
    }

    public function signal(Request $request, LiveSession $liveSession, LiveSessionGuest $guest): JsonResponse
    {
        $guest = $this->guest($request, $liveSession, $guest);
        $data = $request->validate([
            'recipient_key' => ['required', 'string', 'regex:/^(user|guest):[1-9][0-9]*$/'],
            'type' => ['required', Rule::in(['offer', 'answer', 'ice'])], 'payload' => ['required', 'array'],
        ]);
        abort_if(strlen(json_encode($data['payload'], JSON_THROW_ON_ERROR)) > 65_535, 422);
        [$type, $id] = explode(':', $data['recipient_key'], 2);
        $exists = $type === 'user'
            ? $liveSession->participants()->where('user_id', $id)->where('admission_status', ParticipantAdmissionStatus::Admitted->value)->exists()
            : LiveSessionGuest::query()->where('live_session_id', $liveSession->id)->whereKey($id)->where('admission_status', ParticipantAdmissionStatus::Admitted->value)->exists();
        abort_unless($exists, 422);
        LiveSessionGuestSignal::query()->create([
            'live_session_id' => $liveSession->id, 'sender_key' => 'guest:'.$guest->id,
            'recipient_key' => $data['recipient_key'], 'type' => $data['type'], 'payload' => $data['payload'],
        ]);

        return response()->json([], 202);
    }

    public function inbox(Request $request, LiveSession $liveSession, LiveSessionGuest $guest): JsonResponse
    {
        $guest = $this->guest($request, $liveSession, $guest);
        $signals = DB::transaction(function () use ($liveSession, $guest) {
            $items = LiveSessionGuestSignal::query()->lockForUpdate()->where('live_session_id', $liveSession->id)
                ->where('recipient_key', 'guest:'.$guest->id)->whereNull('consumed_at')->oldest()->limit(100)->get();
            LiveSessionGuestSignal::query()->whereKey($items->modelKeys())->update(['consumed_at' => now()]);

            return $items->map(fn ($item) => ['id' => $item->id, 'sender_key' => $item->sender_key, 'type' => $item->type, 'payload' => $item->payload]);
        });

        return response()->json(['signals' => $signals]);
    }

    private function guest(Request $request, LiveSession $session, LiveSessionGuest $guest): LiveSessionGuest
    {
        abort_unless($session->isLive() && (int) $guest->live_session_id === (int) $session->id, 409);
        $resolved = $this->guests->resolveGuest($guest->id, (string) $request->input('token'));
        abort_unless($resolved->admission_status === ParticipantAdmissionStatus::Admitted, 403);

        return $resolved;
    }

    private function participants(LiveSession $session): array
    {
        $users = $session->participants()->where('admission_status', ParticipantAdmissionStatus::Admitted->value)
            ->where('last_seen_at', '>=', now()->subSeconds(20))->with('user:id,name')->get()
            ->map(fn (LiveSessionParticipant $item) => [
                'key' => 'user:'.$item->user_id, 'name' => $item->user?->name, 'role' => $item->role->value,
                'microphone_enabled' => $item->microphone_enabled, 'camera_enabled' => $item->camera_enabled, 'screen_sharing' => $item->screen_sharing,
            ]);
        $guests = LiveSessionGuest::query()->where('live_session_id', $session->id)
            ->where('admission_status', ParticipantAdmissionStatus::Admitted->value)->where('last_seen_at', '>=', now()->subSeconds(20))->get()
            ->map(fn (LiveSessionGuest $item) => [
                'key' => 'guest:'.$item->id, 'name' => $item->name, 'role' => 'guest',
                'microphone_enabled' => $item->microphone_enabled, 'camera_enabled' => $item->camera_enabled, 'screen_sharing' => $item->screen_sharing,
            ]);

        return $users->concat($guests)->values()->all();
    }
}
