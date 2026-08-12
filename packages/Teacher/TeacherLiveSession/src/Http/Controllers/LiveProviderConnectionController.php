<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Services\LiveProviderOAuthService;

final class LiveProviderConnectionController extends Controller
{
    public function __construct(private readonly LiveProviderOAuthService $oauth) {}

    public function connect(string $provider)
    {
        return redirect()->away($this->oauth->authorizationUrl($this->provider($provider)));
    }

    public function callback(Request $request, string $provider)
    {
        $data = $request->validate(['code' => ['required', 'string'], 'state' => ['required', 'string']]);
        $this->oauth->connect($request->user(), $this->provider($provider), $data['code'], $data['state']);

        return redirect()->route('teacher.live-sessions.create')->with('success', __('teacher-live-session::app.provider_connected'));
    }

    public function destroy(Request $request, string $provider)
    {
        $this->oauth->disconnect($request->user(), $this->provider($provider));

        return back()->with('success', __('teacher-live-session::app.provider_disconnected'));
    }

    private function provider(string $value): LiveSessionProvider
    {
        $provider = LiveSessionProvider::tryFrom($value);
        abort_unless($provider?->isExternal(), 404);

        return $provider;
    }
}
