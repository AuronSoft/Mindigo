<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionRecording;
use Mindigo\TeacherLiveSession\Services\LiveSessionAccessService;
use Mindigo\TeacherLiveSession\Services\LiveSessionJoinTokenService;
use Mindigo\TeacherLiveSession\Services\LiveSessionRecordingService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class LiveSessionRecordingController extends Controller
{
    public function __construct(
        private readonly LiveSessionAccessService $access,
        private readonly LiveSessionJoinTokenService $tokens,
        private readonly LiveSessionRecordingService $recordings,
    ) {}

    public function start(Request $request, LiveSession $liveSession): JsonResponse
    {
        $this->authorizeModerator($request, $liveSession);
        $data = $request->validate(['token' => ['required', 'string', 'max:4096'], 'mime_type' => ['required', Rule::in(['video/webm', 'video/webm;codecs=vp8,opus', 'video/webm;codecs=vp9,opus', 'video/mp4'])]]);
        $recording = $this->recordings->start($liveSession, $request->user(), $data['mime_type']);

        return response()->json(['recording_id' => $recording->id, 'capture_mode' => $recording->capture_mode], 201);
    }

    public function stop(Request $request, LiveSession $liveSession, LiveSessionRecording $recording): JsonResponse
    {
        $this->authorizeModerator($request, $liveSession);
        abort_unless((int) $recording->live_session_id === (int) $liveSession->id, 404);
        $recording = $this->recordings->stopServer($recording, $request->user());

        return response()->json(['status' => $recording->status, 'progress' => $recording->progress], 202);
    }

    public function status(Request $request, LiveSessionRecording $recording): JsonResponse
    {
        $this->authorizePlayback($request, $recording);

        return response()->json([
            'status' => $recording->status, 'progress' => $recording->progress,
            'playback_url' => $recording->status === 'ready' ? route('live-recordings.stream', $recording) : null,
            'hls_url' => $recording->status === 'ready' && $recording->hls_manifest_path ? route('live-recordings.hls', [$recording, 'path' => 'master.m3u8']) : null,
        ]);
    }

    public function chunk(Request $request, LiveSession $liveSession, LiveSessionRecording $recording): JsonResponse
    {
        $this->authorizeModerator($request, $liveSession);
        $data = $request->validate([
            'token' => ['required', 'string', 'max:4096'], 'sequence' => ['required', 'integer', 'min:0'],
            'checksum' => ['required', 'string', 'size:64', 'regex:/^[a-f0-9]+$/'], 'chunk' => ['required', 'file', 'max:10240'],
        ]);
        abort_unless((int) $recording->live_session_id === (int) $liveSession->id, 404);
        $this->recordings->storeChunk($recording, $request->user(), (int) $data['sequence'], $data['chunk'], $data['checksum']);

        return response()->json([], 202);
    }

    public function finalize(Request $request, LiveSession $liveSession, LiveSessionRecording $recording): JsonResponse
    {
        $this->authorizeModerator($request, $liveSession);
        $data = $request->validate([
            'token' => ['required', 'string', 'max:4096'], 'duration_seconds' => ['required', 'integer', 'min:1', 'max:86400'],
            'expected_chunks' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);
        abort_unless((int) $recording->live_session_id === (int) $liveSession->id, 404);
        $ready = $this->recordings->finalize($recording, $request->user(), (int) $data['duration_seconds'], (int) $data['expected_chunks']);

        return response()->json(['status' => $ready->status, 'playback_url' => route('live-recordings.stream', $ready)]);
    }

    public function abort(Request $request, LiveSession $liveSession, LiveSessionRecording $recording): JsonResponse
    {
        $this->authorizeModerator($request, $liveSession);
        abort_unless((int) $recording->live_session_id === (int) $liveSession->id, 404);
        $this->recordings->fail($recording, $request->user());

        return response()->json([], 202);
    }

    public function stream(Request $request, LiveSessionRecording $recording): BinaryFileResponse
    {
        abort_unless($recording->status === 'ready' && $recording->storage_path, 404);
        $this->authorizePlayback($request, $recording);
        abort_unless($recording->storage_disk === 'local' && Storage::disk('local')->exists($recording->storage_path), 404);

        return response()->file(Storage::disk('local')->path($recording->storage_path), [
            'Content-Type' => $recording->mime_type,
            'Content-Disposition' => 'inline; filename="'.basename($recording->storage_path).'"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function hls(Request $request, LiveSessionRecording $recording, string $path): BinaryFileResponse
    {
        abort_unless($recording->status === 'ready' && $recording->hls_manifest_path, 404);
        $this->authorizePlayback($request, $recording);
        abort_unless(preg_match('/^(master\.m3u8|variant-[01]\.m3u8|variant-[01]-segment-[0-9]{5}\.ts)$/', $path) === 1, 404);
        $file = dirname($recording->hls_manifest_path).'/'.$path;
        abort_unless($recording->storage_disk === 'local' && Storage::disk('local')->exists($file), 404);

        return response()->file(Storage::disk('local')->path($file), [
            'Content-Type' => str_ends_with($path, '.m3u8') ? 'application/vnd.apple.mpegurl' : 'video/mp2t',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    private function authorizePlayback(Request $request, LiveSessionRecording $recording): void
    {
        $session = $recording->session()->with('classroom')->firstOrFail();
        $user = $request->user();
        $allowed = $user->isAdmin() || $this->access->canManage($session, $user)
            || (int) $session->classroom?->assistant_id === (int) $user->id
            || $session->classroom?->students()->whereKey($user->id)->wherePivot('status', 'active')->exists();
        abort_unless($allowed, 403);
    }

    private function authorizeModerator(Request $request, LiveSession $session): void
    {
        abort_unless($this->access->canModerate($session, $request->user()), 403);
        $this->tokens->validate((string) $request->input('token'), $session, $request->user());
    }
}
