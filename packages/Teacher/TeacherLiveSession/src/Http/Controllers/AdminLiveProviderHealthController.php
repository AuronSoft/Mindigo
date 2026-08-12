<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Services\LiveMeetingProviderRegistry;
use Mindigo\TeacherLiveSession\Services\LiveProviderCircuitBreaker;

final class AdminLiveProviderHealthController extends Controller
{
    public function index(LiveMeetingProviderRegistry $providers, LiveProviderCircuitBreaker $circuit): View
    {
        $health = collect([LiveSessionProvider::Native, LiveSessionProvider::GoogleMeet, LiveSessionProvider::Zoom])
            ->map(function (LiveSessionProvider $provider) use ($providers, $circuit): array {
                $reported = $providers->resolve($provider)->health();
                $state = $circuit->state($provider);

                return [
                    'provider' => $provider, 'configured' => $reported->available, 'message' => $reported->message,
                    'circuit' => $state,
                    'pending' => LiveSession::query()->where('provider', $provider->value)->where('sync_status', 'pending')->count(),
                    'failed' => LiveSession::query()->where('provider', $provider->value)->where('sync_status', 'failed')->count(),
                    'available' => $provider === LiveSessionProvider::Native || ($reported->available && $state['available']),
                ];
            });

        return view('teacher-live-session::admin.provider-health', compact('health'));
    }

    public function reset(string $provider, LiveProviderCircuitBreaker $circuit): RedirectResponse
    {
        $resolved = LiveSessionProvider::tryFrom($provider);
        abort_unless($resolved?->isExternal(), 404);
        $circuit->reset($resolved);

        return back()->with('success', __('teacher-live-session::app.provider_circuit_reset'));
    }
}
