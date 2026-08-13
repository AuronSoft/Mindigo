<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\LiveSession\ProcessLiveProviderWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mindigo\TeacherLiveSession\Models\LiveProviderWebhookEvent;
use Mindigo\TeacherLiveSession\Services\LiveProviderWebhookVerifier;

final class LiveProviderWebhookController extends Controller
{
    public function __construct(private readonly LiveProviderWebhookVerifier $verifier) {}

    public function zoom(Request $request): JsonResponse|Response
    {
        $this->verifier->verifyZoom($request);
        if ($request->string('event')->toString() === 'endpoint.url_validation') {
            $plainToken = (string) data_get($request->json()->all(), 'payload.plainToken');

            return response()->json([
                'plainToken' => $plainToken,
                'encryptedToken' => hash_hmac('sha256', $plainToken, (string) config('live-providers.zoom.webhook_secret')),
            ]);
        }

        $payload = $request->json()->all();
        $eventId = (string) ($request->header('x-zm-trackingid') ?: hash('sha256', $request->getContent()));
        $this->store('zoom', $eventId, (string) ($payload['event'] ?? 'unknown'), $payload);

        return response()->noContent();
    }

    public function googleCalendar(Request $request): Response
    {
        $this->verifier->verifyGoogleCalendar($request);
        $eventId = implode(':', [(string) $request->header('x-goog-channel-id'), (string) $request->header('x-goog-message-number')]);
        $this->store('google_meet', $eventId, 'calendar.changed', [
            'resource_id' => $request->header('x-goog-resource-id'),
            'resource_state' => $request->header('x-goog-resource-state'),
        ]);

        return response()->noContent();
    }

    public function googleMeet(Request $request): Response
    {
        $this->verifier->verifyGoogleMeet($request);
        $envelope = $request->json()->all();
        $message = data_get($envelope, 'message', []);
        $decoded = base64_decode((string) data_get($message, 'data', ''), true);
        $resource = $decoded === false ? [] : (json_decode($decoded, true) ?: []);
        $type = (string) (data_get($message, 'attributes.ce-type') ?? data_get($resource, 'type') ?? 'google.workspace.meet.unknown');
        $eventId = (string) (data_get($message, 'messageId') ?? data_get($resource, 'id') ?? hash('sha256', $request->getContent()));
        $this->store('google_meet', $eventId, $type, $resource);

        return response()->noContent();
    }

    private function store(string $provider, string $eventId, string $type, array $payload): void
    {
        $event = LiveProviderWebhookEvent::query()->firstOrCreate(
            ['provider' => $provider, 'event_id' => $eventId],
            ['event_type' => $type, 'payload' => $payload, 'status' => 'pending', 'received_at' => now()],
        );
        if ($event->wasRecentlyCreated) {
            ProcessLiveProviderWebhook::dispatch($event->id)->afterCommit();
        }
    }
}
