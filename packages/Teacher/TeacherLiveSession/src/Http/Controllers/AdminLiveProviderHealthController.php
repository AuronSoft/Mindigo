<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\TeacherLiveSession\Data\ProviderCapabilities;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Http\Requests\UpdateLiveSessionConfigurationRequest;
use Mindigo\TeacherLiveSession\Models\LiveProviderConnection;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Services\LiveMeetingProviderRegistry;
use Mindigo\TeacherLiveSession\Services\LiveProviderCircuitBreaker;
use Mindigo\TeacherLiveSession\Services\LiveProviderErrorSanitizer;
use Mindigo\TeacherLiveSession\Services\LiveSessionConfigurationService;

final class AdminLiveProviderHealthController extends Controller
{
    public function index(
        LiveMeetingProviderRegistry $providers,
        LiveProviderCircuitBreaker $circuit,
        LiveSessionConfigurationService $configuration,
        LiveProviderErrorSanitizer $errors,
    ): View {
        $health = collect([LiveSessionProvider::Native, LiveSessionProvider::GoogleMeet, LiveSessionProvider::Zoom])
            ->map(function (LiveSessionProvider $provider) use ($providers, $circuit, $configuration): array {
                $reported = $providers->resolve($provider)->health();
                $state = $circuit->state($provider);
                $capabilities = $providers->resolve($provider)->capabilities();

                return [
                    'provider' => $provider, 'configured' => $reported->available, 'message' => $reported->message,
                    'enabled' => $configuration->providerEnabled($provider),
                    'capabilities' => $this->capabilities($capabilities),
                    'circuit' => $state,
                    'pending' => LiveSession::query()->where('provider', $provider->value)->where('sync_status', 'pending')->count(),
                    'failed' => LiveSession::query()->where('provider', $provider->value)->where('sync_status', 'failed')->count(),
                    'connections' => LiveProviderConnection::query()->where('provider', $provider->value)->whereNull('revoked_at')->count(),
                    'monthly_sessions' => LiveSession::query()->where('provider', $provider->value)->where('created_at', '>=', now()->startOfMonth())->count(),
                    'available' => $configuration->providerEnabled($provider) && ($provider === LiveSessionProvider::Native || ($reported->available && $state['available'])),
                ];
            });

        $recentErrors = LiveSession::query()->whereNotNull('sync_error')->latest('last_synced_at')->limit(20)->get()
            ->map(fn (LiveSession $session) => ['session' => $session->title, 'provider' => $session->provider->value, 'at' => $session->last_synced_at, 'message' => $errors->from($session->sync_error)]);

        return view('teacher-live-session::admin.provider-health', compact('health', 'recentErrors'));
    }

    public function configuration(LiveSessionConfigurationService $configuration): View
    {
        return view('teacher-live-session::admin.configuration', [
            'settings' => $configuration->all(),
            'credentials' => [
                'google_meet' => filled(config('live-providers.google_meet.client_id')) && filled(config('live-providers.google_meet.client_secret')),
                'zoom' => filled(config('live-providers.zoom.client_id')) && filled(config('live-providers.zoom.client_secret')),
            ],
        ]);
    }

    public function updateConfiguration(
        UpdateLiveSessionConfigurationRequest $request,
        LiveSessionConfigurationService $configuration,
        AuditLogService $audit,
    ): RedirectResponse {
        $changes = $configuration->update($request->validated());
        $audit->record('updated', 'live_session_configuration', $changes['before'], $changes['after'], user: $request->user());

        return back()->with('success', __('teacher-live-session::app.configuration_saved'));
    }

    public function reset(string $provider, LiveProviderCircuitBreaker $circuit): RedirectResponse
    {
        $resolved = LiveSessionProvider::tryFrom($provider);
        abort_unless($resolved?->isExternal(), 404);
        $circuit->reset($resolved);

        return back()->with('success', __('teacher-live-session::app.provider_circuit_reset'));
    }

    private function capabilities(ProviderCapabilities $capabilities): array
    {
        return ['embedded' => $capabilities->embedded, 'guest_links' => $capabilities->guestLinks,
            'attendance_sync' => $capabilities->attendanceSync, 'recording' => $capabilities->recording];
    }
}
