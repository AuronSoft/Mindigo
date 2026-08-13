<?php

namespace Tests\Feature;

use App\Jobs\LiveSession\ProcessServerRecording;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionRecording;
use Mindigo\TeacherLiveSession\Services\LiveSessionRecordingService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class LiveSessionServerRecordingTest extends TestCase
{
    use RefreshDatabase;

    public function test_sfu_recording_is_started_and_stopped_by_the_server_gateway(): void
    {
        config(['live-media.topology' => 'sfu', 'live-media.recording.server_enabled' => true, 'live-media.gateway.secret' => str_repeat('a', 32)]);
        Http::fake([
            '*/recordings/start' => Http::response(['recording_id' => 'gateway-recording-1'], 201),
            '*/recordings/gateway-recording-1/stop' => Http::response(['source_path' => 'server-recordings/gateway-recording-1/manifest.json', 'duration_seconds' => 90]),
        ]);
        Bus::fake();
        [$session, $teacher] = $this->room();

        $recording = app(LiveSessionRecordingService::class)->start($session, $teacher, 'video/webm');
        $this->assertSame('server', $recording->capture_mode);
        $this->assertSame('gateway-recording-1', $recording->gateway_recording_id);

        $recording = app(LiveSessionRecordingService::class)->stopServer($recording, $teacher);
        $this->assertSame('processing', $recording->status);
        $this->assertSame(90, $recording->duration_seconds);
        Bus::assertDispatched(ProcessServerRecording::class, fn ($job) => $job->recordingId === $recording->id);
    }

    public function test_monthly_storage_quota_blocks_new_recording(): void
    {
        config(['live-media.recording.monthly_quota_gb' => 1]);
        [$session, $teacher] = $this->room();
        LiveSessionRecording::query()->create(['live_session_id' => $session->id, 'initiated_by' => $teacher->id, 'status' => 'ready', 'mime_type' => 'video/mp4', 'storage_disk' => 'local', 'size_bytes' => 1024 * 1024 * 1024, 'started_at' => now()]);

        $this->expectException(HttpException::class);
        app(LiveSessionRecordingService::class)->start($session, $teacher, 'video/webm');
    }

    public function test_authorized_teacher_can_read_private_recording_status_and_hls_manifest(): void
    {
        Storage::fake('local');
        [$session, $teacher] = $this->room();
        $manifest = 'live-recordings/'.$session->id.'/1/hls/master.m3u8';
        Storage::disk('local')->put($manifest, "#EXTM3U\n");
        Storage::disk('local')->put('live-recordings/'.$session->id.'/1/recording.mp4', 'recording');
        $recording = LiveSessionRecording::query()->create([
            'live_session_id' => $session->id,
            'initiated_by' => $teacher->id,
            'capture_mode' => 'server',
            'status' => 'ready',
            'progress' => 100,
            'mime_type' => 'video/mp4',
            'storage_disk' => 'local',
            'storage_path' => 'live-recordings/'.$session->id.'/1/recording.mp4',
            'hls_manifest_path' => $manifest,
            'started_at' => now()->subMinute(),
            'ended_at' => now(),
        ]);

        $status = $this->actingAs($teacher)->getJson(route('live-recordings.status', $recording));
        $status->assertOk()->assertJsonPath('status', 'ready')->assertJsonPath('progress', 100);
        $this->actingAs($teacher)
            ->get(route('live-recordings.hls', [$recording, 'path' => 'master.m3u8']))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.apple.mpegurl');
    }

    private function room(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE, 'name' => 'Server recording', 'code' => 'REC-'.str()->random(6), 'slug' => 'rec-'.str()->random(8), 'status' => 'active']);
        $session = LiveSession::query()->create(['classroom_id' => $classroom->id, 'teacher_id' => $teacher->id, 'created_by' => $teacher->id, 'title' => 'Server recording', 'room_name' => 'server-recording', 'provider' => 'native', 'provider_status' => 'live', 'fallback_provider' => 'native', 'sync_status' => 'not_required', 'session_type' => 'flexible', 'scheduled_start' => now()->subMinute(), 'scheduled_end' => now()->addHour(), 'status' => 'live', 'room_settings' => ['recording_enabled' => true]]);

        return [$session, $teacher];
    }
}
