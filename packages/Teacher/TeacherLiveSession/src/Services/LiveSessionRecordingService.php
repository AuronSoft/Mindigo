<?php

namespace Mindigo\TeacherLiveSession\Services;

use App\Jobs\LiveSession\ProcessServerRecording;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionRecording;
use Mindigo\TeacherLiveSession\Models\LiveSessionRecordingChunk;
use RuntimeException;

final class LiveSessionRecordingService
{
    public function __construct(
        private readonly AuditLogService $audit,
        private readonly LiveSessionConfigurationService $configuration,
        private readonly LiveRecordingQuotaService $quota,
        private readonly LiveServerRecordingGateway $gateway,
    ) {}

    public function start(LiveSession $session, User $actor, string $mimeType): LiveSessionRecording
    {
        abort_if(! $this->configuration->value('live_recording_enabled') || ($session->room_settings['recording_enabled'] ?? false) !== true || ! $session->isLive(), 422);
        $this->quota->assertAvailable((int) $session->teacher_id);
        if ($this->configuration->value('live_recording_consent_required')) {
            $missingConsent = $session->participants()->where('admission_status', 'admitted')->whereNull('recording_consented_at')->exists()
                || $session->guests()->where('admission_status', 'admitted')->whereNull('recording_consented_at')->exists();
            abort_if($missingConsent, 422, __('teacher-live-session::app.validation.recording_consent_missing'));
        }
        $serverCapture = config('live-media.topology') === 'sfu' && (bool) config('live-media.recording.server_enabled');
        $recording = DB::transaction(function () use ($session, $actor, $mimeType, $serverCapture): LiveSessionRecording {
            LiveSession::query()->lockForUpdate()->findOrFail($session->id);
            if (LiveSessionRecording::query()->where('live_session_id', $session->id)->whereIn('status', ['recording', 'processing'])->exists()) {
                throw ValidationException::withMessages(['recording' => __('teacher-live-session::app.validation.recording_already_active')]);
            }

            return LiveSessionRecording::query()->create([
                'live_session_id' => $session->id, 'initiated_by' => $actor->id,
                'status' => 'recording', 'capture_mode' => $serverCapture ? 'server' : 'client', 'progress' => 0,
                'mime_type' => $mimeType, 'storage_disk' => config('live-media.recording.disk', 'local'), 'started_at' => now(),
            ]);
        });
        if ($serverCapture) {
            try {
                $capture = $this->gateway->start($recording);
                $recording->update(['gateway_recording_id' => $capture['recording_id'], 'progress' => 5]);
            } catch (\Throwable $exception) {
                $recording->update(['status' => 'failed', 'failure_reason' => 'gateway_start_failed']);
                throw $exception;
            }
        }
        $this->audit->record('recording_started', 'teacher_live_session', metadata: ['recording_id' => $recording->id], auditable: $session, user: $actor);

        return $recording;
    }

    public function stopServer(LiveSessionRecording $recording, User $actor): LiveSessionRecording
    {
        abort_unless($recording->capture_mode === 'server' && $recording->status === 'recording' && (int) $recording->initiated_by === (int) $actor->id, 403);
        $capture = $this->gateway->stop($recording);
        $recording->update([
            'status' => 'processing', 'progress' => 8, 'source_path' => $capture['source_path'],
            'duration_seconds' => $capture['duration_seconds'] ?? null, 'ended_at' => now(),
        ]);
        ProcessServerRecording::dispatch($recording->id)->afterCommit();

        return $recording->fresh();
    }

    public function storeChunk(LiveSessionRecording $recording, User $actor, int $sequence, UploadedFile $chunk, string $checksum): void
    {
        abort_unless($recording->capture_mode === 'client', 409);
        abort_unless($recording->status === 'recording' && (int) $recording->initiated_by === (int) $actor->id, 403);
        $actualChecksum = hash_file('sha256', $chunk->getRealPath());
        if (! hash_equals($actualChecksum, $checksum)) {
            throw ValidationException::withMessages(['checksum' => __('teacher-live-session::app.validation.recording_checksum')]);
        }
        $existing = $recording->chunks()->where('sequence', $sequence)->first();
        if ($existing) {
            if (! hash_equals($existing->checksum, $actualChecksum)) {
                throw ValidationException::withMessages(['sequence' => __('teacher-live-session::app.validation.recording_chunk_conflict')]);
            }

            return;
        }
        $path = $chunk->storeAs('live-recordings/'.$recording->id.'/chunks', str_pad((string) $sequence, 8, '0', STR_PAD_LEFT).'.part', 'local');
        LiveSessionRecordingChunk::query()->create([
            'recording_id' => $recording->id, 'sequence' => $sequence, 'storage_path' => $path,
            'size_bytes' => $chunk->getSize(), 'checksum' => $actualChecksum,
        ]);
    }

    public function finalize(LiveSessionRecording $recording, User $actor, int $durationSeconds, int $expectedChunks): LiveSessionRecording
    {
        abort_unless($recording->capture_mode === 'client', 409);
        abort_unless($recording->status === 'recording' && (int) $recording->initiated_by === (int) $actor->id, 403);
        abort_if($durationSeconds > ((int) $this->configuration->value('live_recording_max_minutes') * 60), 422, __('teacher-live-session::app.validation.recording_duration_limit'));
        $chunks = $recording->chunks()->orderBy('sequence')->get();
        if ($chunks->count() !== $expectedChunks || $chunks->pluck('sequence')->values()->all() !== range(0, $expectedChunks - 1)) {
            throw ValidationException::withMessages(['chunks' => __('teacher-live-session::app.validation.recording_chunks_incomplete')]);
        }
        $recording->update(['status' => 'processing']);
        $temporary = tmpfile();
        if ($temporary === false) {
            throw new RuntimeException('Unable to allocate recording stream.');
        }
        foreach ($chunks as $chunk) {
            $stream = Storage::disk('local')->readStream($chunk->storage_path);
            if ($stream === false) {
                throw ValidationException::withMessages(['chunks' => __('teacher-live-session::app.validation.recording_chunks_incomplete')]);
            }
            stream_copy_to_stream($stream, $temporary);
            fclose($stream);
        }
        rewind($temporary);
        $extension = str_contains($recording->mime_type, 'mp4') ? 'mp4' : 'webm';
        $finalPath = 'live-recordings/'.$recording->live_session_id.'/'.$recording->id.'.'.$extension;
        Storage::disk('local')->put($finalPath, $temporary);
        fclose($temporary);
        $size = Storage::disk('local')->size($finalPath);
        Storage::disk('local')->delete($chunks->pluck('storage_path')->all());
        $recording->chunks()->delete();
        $recording->update([
            'status' => 'ready', 'storage_path' => $finalPath, 'size_bytes' => $size,
            'duration_seconds' => $durationSeconds, 'ended_at' => now(),
        ]);
        $this->audit->record('recording_ready', 'teacher_live_session', metadata: ['recording_id' => $recording->id, 'size_bytes' => $size], auditable: $recording->session, user: $actor);

        return $recording->fresh();
    }

    public function fail(LiveSessionRecording $recording, User $actor): void
    {
        abort_unless(in_array($recording->status, ['recording', 'processing'], true) && (int) $recording->initiated_by === (int) $actor->id, 403);
        $paths = $recording->chunks()->pluck('storage_path')->all();
        Storage::disk($recording->storage_disk)->delete($paths);
        $recording->chunks()->delete();
        $recording->update(['status' => 'failed', 'ended_at' => now(), 'failure_reason' => 'client_aborted']);
        $this->audit->record('recording_failed', 'teacher_live_session', metadata: ['recording_id' => $recording->id], auditable: $recording->session, user: $actor);
    }
}
