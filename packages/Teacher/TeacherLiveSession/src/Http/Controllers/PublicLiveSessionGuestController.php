<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Services\LiveSessionGuestService;

final class PublicLiveSessionGuestController extends Controller
{
    public function __construct(private readonly LiveSessionGuestService $guests) {}

    public function show(string $token)
    {
        $link = $this->guests->resolveLink($token);

        return view('teacher-live-session::guest.join', compact('link', 'token'));
    }

    public function join(Request $request, string $token): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'min:2', 'max:120'], 'email' => ['nullable', 'email:rfc', 'max:255']]);
        $result = $this->guests->register($this->guests->resolveLink($token), $data['name'], $data['email'] ?? null);
        $request->session()->put('live_guest.'.$result['guest']->id, $result['access_token']);

        return redirect()->route('live-guest.status', $result['guest']);
    }

    public function status(Request $request, int $guest)
    {
        $model = $this->resolveFromSession($request, $guest);
        if (in_array($model->admission_status, [ParticipantAdmissionStatus::Denied, ParticipantAdmissionStatus::Removed], true)) {
            abort(403);
        }

        $mediaConfig = null;
        if ($model->admission_status === ParticipantAdmissionStatus::Admitted && $model->session->isLive()) {
            $mediaConfig = [
                'participantKey' => 'guest:'.$model->id, 'connectionId' => (string) Str::uuid(),
                'token' => $request->session()->get('live_guest.'.$guest),
                'presenceUrl' => route('live-guest-media.presence', [$model->session, $model]),
                'signalUrl' => route('live-guest-media.signals.store', [$model->session, $model]),
                'inboxUrl' => route('live-guest-media.signals.inbox', [$model->session, $model]),
                'leaveUrl' => route('live-guest.status', $model), 'iceServers' => config('live-media.ice_servers', []),
            ];
        }

        return view('teacher-live-session::guest.status', ['guest' => $model, 'mediaConfig' => $mediaConfig]);
    }

    private function resolveFromSession(Request $request, int $guest)
    {
        $accessToken = $request->session()->get('live_guest.'.$guest);
        abort_unless(is_string($accessToken), 403);

        return $this->guests->resolveGuest($guest, $accessToken);
    }
}
