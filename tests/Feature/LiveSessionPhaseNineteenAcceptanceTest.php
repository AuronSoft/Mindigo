<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionMessage;
use Mindigo\TeacherLiveSession\Services\LiveSessionArchiveService;
use Tests\TestCase;

class LiveSessionPhaseNineteenAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_backup_is_valid_redacted_and_restorable(): void
    {
        Storage::fake('local');
        [$session, $teacher] = $this->room();
        $message = LiveSessionMessage::query()->create(['live_session_id' => $session->id, 'sender_id' => $teacher->id, 'body' => 'Durable message']);
        $archives = app(LiveSessionArchiveService::class);

        $backup = $archives->backup();
        $inspection = $archives->inspect($backup['path']);
        $sessionRow = collect($inspection['archive']['tables']['live_sessions'])->firstWhere('id', $session->id);

        Storage::disk('local')->assertExists($backup['path']);
        $this->assertArrayNotHasKey('provider_host_url', $sessionRow);
        $this->assertArrayNotHasKey('sync_error', $sessionRow);
        $metadata = json_decode($sessionRow['provider_metadata'], true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('safe-id', $metadata['uuid']);
        $this->assertArrayNotHasKey('password', $metadata);
        $this->assertArrayNotHasKey('access_token', $metadata['nested']);
        $message->delete();
        $this->assertSame(1, $archives->restore($backup['path']));
        $this->assertDatabaseHas('live_session_messages', ['id' => $message->id, 'body' => 'Durable message']);
    }

    public function test_backup_and_restore_commands_default_to_non_destructive_inspection(): void
    {
        Storage::fake('local');
        $this->room();
        $this->artisan('live-sessions:backup')->assertSuccessful();
        $archive = collect(Storage::disk('local')->allFiles('live-session-backups'))->sole();

        $this->artisan('live-sessions:restore', ['archive' => $archive])
            ->expectsOutputToContain('Inspection only')
            ->assertSuccessful();
    }

    public function test_creation_form_contains_stable_idempotency_boundary_and_accessibility_labels(): void
    {
        [, $teacher] = $this->room();

        $response = $this->actingAs($teacher)->get(route('teacher.live-sessions.create'));

        $response->assertOk()
            ->assertSee('name="idempotency_key"', false)
            ->assertSee('role="tablist"', false)
            ->assertSee('aria-label=', false);
    }

    private function room(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Phase 19', 'code' => 'P19-'.str()->random(6), 'slug' => 'p19-'.str()->random(8), 'status' => 'active',
        ]);
        $session = LiveSession::query()->create([
            'classroom_id' => $classroom->id, 'teacher_id' => $teacher->id, 'created_by' => $teacher->id,
            'title' => 'Acceptance room', 'room_name' => 'acceptance-room', 'provider' => 'zoom',
            'provider_host_url' => 'https://zoom.us/s/123?zak=secret', 'sync_error' => 'Bearer secret',
            'provider_metadata' => ['uuid' => 'safe-id', 'password' => 'meeting-secret', 'nested' => ['access_token' => 'oauth-secret']],
            'provider_status' => 'waiting', 'fallback_provider' => 'native', 'sync_status' => 'failed',
            'session_type' => 'flexible', 'scheduled_start' => now()->addDay(), 'scheduled_end' => now()->addDay()->addHour(),
            'status' => 'scheduled', 'room_settings' => [],
        ]);

        return [$session, $teacher];
    }
}
