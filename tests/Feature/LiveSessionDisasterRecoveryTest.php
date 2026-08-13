<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionRecording;
use Mindigo\TeacherLiveSession\Models\LiveSessionResource;
use Mindigo\TeacherLiveSession\Services\LiveSessionDisasterRecoveryService;
use RuntimeException;
use Tests\TestCase;

final class LiveSessionDisasterRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config([
            'live-disaster-recovery.disk' => 'local',
            'live-disaster-recovery.encryption_key' => base64_encode(random_bytes(32)),
            'live-disaster-recovery.retention_copies' => 2,
            'live-disaster-recovery.include_media' => true,
        ]);
    }

    public function test_encrypted_archive_contains_full_database_and_live_media(): void
    {
        [$session, $teacher] = $this->room();
        Storage::disk('local')->put('live-recordings/demo/recording.mp4', 'private recording bytes');
        Storage::disk('local')->put('live-session-resources/demo/slides.pdf', 'private resource bytes');
        LiveSessionRecording::query()->create([
            'live_session_id' => $session->id, 'initiated_by' => $teacher->id, 'status' => 'ready',
            'capture_mode' => 'server', 'progress' => 100, 'mime_type' => 'video/mp4', 'storage_disk' => 'local',
            'storage_path' => 'live-recordings/demo/recording.mp4', 'started_at' => now(), 'ended_at' => now(),
        ]);
        LiveSessionResource::query()->create([
            'live_session_id' => $session->id, 'uploaded_by' => $teacher->id, 'name' => 'Slides',
            'mime_type' => 'application/pdf', 'storage_disk' => 'local', 'storage_path' => 'live-session-resources/demo/slides.pdf',
            'size_bytes' => 22, 'checksum' => hash('sha256', 'private resource bytes'),
        ]);

        $result = app(LiveSessionDisasterRecoveryService::class)->backup();
        $this->assertTrue($result['verified']);
        $encrypted = Storage::disk('local')->get($result['path']);
        $this->assertStringStartsWith('MINDIGO-DR-1', $encrypted);
        $this->assertStringNotContainsString('private recording bytes', $encrypted);
        $inspection = app(LiveSessionDisasterRecoveryService::class)->drill($result['path']);

        $this->assertGreaterThan(0, $inspection['tables']);
        $this->assertGreaterThan(0, $inspection['records']);
        $this->assertSame(2, $inspection['files']);
        $this->assertArrayHasKey('users', $inspection['manifest']['database']);
    }

    public function test_restore_command_is_a_non_mutating_drill_without_apply_and_force(): void
    {
        $this->room();
        $archive = app(LiveSessionDisasterRecoveryService::class)->backup()['path'];

        $this->artisan('live-sessions:dr-restore', ['archive' => $archive])->assertSuccessful();
        $this->artisan('live-sessions:dr-restore', ['archive' => $archive, '--apply' => true])->assertFailed();
    }

    public function test_archive_tampering_is_rejected_before_restore(): void
    {
        $this->room();
        $archive = app(LiveSessionDisasterRecoveryService::class)->backup()['path'];
        $payload = Storage::disk('local')->get($archive);
        Storage::disk('local')->put($archive, substr($payload, 0, -1).chr(ord(substr($payload, -1)) ^ 1));

        $this->expectException(RuntimeException::class);
        app(LiveSessionDisasterRecoveryService::class)->drill($archive);
    }

    private function room(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Recovery classroom', 'code' => 'DR-'.str()->random(8), 'slug' => 'dr-'.str()->random(8), 'status' => 'active',
        ]);
        $session = LiveSession::query()->create([
            'classroom_id' => $classroom->id, 'teacher_id' => $teacher->id, 'created_by' => $teacher->id,
            'title' => 'Recovery session', 'room_name' => 'recovery', 'provider' => 'native', 'provider_status' => 'ended',
            'fallback_provider' => 'native', 'sync_status' => 'not_required', 'session_type' => 'flexible',
            'scheduled_start' => now()->subHour(), 'scheduled_end' => now(), 'status' => 'ended',
        ]);

        return [$session, $teacher];
    }
}
