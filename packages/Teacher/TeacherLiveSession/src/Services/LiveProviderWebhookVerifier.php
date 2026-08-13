<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class LiveProviderWebhookVerifier
{
    public function verifyZoom(Request $request): void
    {
        $secret = (string) config('live-providers.zoom.webhook_secret');
        $timestamp = (int) $request->header('x-zm-request-timestamp');
        $signature = (string) $request->header('x-zm-signature');
        $tolerance = (int) config('live-providers.zoom.webhook_tolerance_seconds', 300);

        if ($secret === '' || $timestamp === 0 || abs(now()->timestamp - $timestamp) > $tolerance) {
            throw new AccessDeniedHttpException('Invalid Zoom webhook timestamp.');
        }

        $expected = 'v0='.hash_hmac('sha256', 'v0:'.$timestamp.':'.$request->getContent(), $secret);
        if (! hash_equals($expected, $signature)) {
            throw new AccessDeniedHttpException('Invalid Zoom webhook signature.');
        }
    }

    public function verifyGoogleCalendar(Request $request): void
    {
        $expected = (string) config('live-providers.google_meet.webhook_token');
        $provided = (string) $request->header('x-goog-channel-token');
        if ($expected === '' || ! hash_equals($expected, $provided)) {
            throw new AccessDeniedHttpException('Invalid Google webhook token.');
        }
    }

    public function verifyGoogleMeet(Request $request): void
    {
        $expected = (string) config('live-providers.google_meet.pubsub_verification_token');
        $provided = (string) $request->bearerToken();
        if ($expected === '' || ! hash_equals($expected, $provided)) {
            throw new AccessDeniedHttpException('Invalid Google Pub/Sub verification token.');
        }
    }
}
