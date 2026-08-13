<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Mindigo\TeacherLiveSession\Models\LiveSessionRecording;

final class LiveServerRecordingGateway
{
    public function start(LiveSessionRecording $recording): array
    {
        return $this->request()->post('/recordings/start', [
            'recording_id' => $recording->id,
            'session_id' => $recording->live_session_id,
        ])->throw()->json();
    }

    public function stop(LiveSessionRecording $recording): array
    {
        return $this->request()->post('/recordings/'.$recording->gateway_recording_id.'/stop')->throw()->json();
    }

    private function request(): PendingRequest
    {
        $timestamp = (string) now()->timestamp;
        $secret = (string) config('live-media.gateway.secret');

        return Http::baseUrl(rtrim((string) config('live-media.gateway.control_url'), '/'))
            ->acceptJson()->timeout(15)->withHeaders([
                'X-Mindigo-Timestamp' => $timestamp,
                'X-Mindigo-Signature' => hash_hmac('sha256', $timestamp, $secret),
            ]);
    }
}
