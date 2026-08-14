<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\AuditLog\Models\AuditLog;
use Mindigo\SystemSetting\Models\SystemSetting;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionMessage;
use Mindigo\TeacherLiveSession\Services\LiveMeetingProviderRegistry;
use Mindigo\TeacherLiveSession\Services\LiveProviderErrorSanitizer;
use Tests\TestCase;

class LiveSessionAdministrationConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_manage_live_classroom_configuration(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($teacher)->get(route('admin.live-providers.configuration'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.live-providers.configuration'))
            ->assertOk()
            ->assertSee('Mindigo Live')
            ->assertSee('Google Meet OAuth');
    }

    public function test_admin_updates_limits_and_disabled_provider_is_removed_while_native_remains(): void
    {
        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.live-providers.configuration.update'), $this->settings([
            'live_google_meet_enabled' => 0,
            'live_zoom_enabled' => 0,
        ]))->assertRedirect();

        $this->assertSame('48', SystemSetting::query()->where('key', 'live_max_participants')->value('value'));
        $this->assertSame(['native'], array_keys(app(LiveMeetingProviderRegistry::class)->capabilities()));
        $this->assertDatabaseHas((new AuditLog)->getTable(), ['module' => 'live_session_configuration']);
    }

    public function test_provider_errors_are_redacted_before_they_are_displayed_or_stored(): void
    {
        $sanitizer = app(LiveProviderErrorSanitizer::class);
        $message = $sanitizer->from('Bearer secret-token client_secret=very-secret&access_token=access-secret');

        $this->assertStringNotContainsString('secret-token', $message);
        $this->assertStringNotContainsString('very-secret', $message);
        $this->assertStringNotContainsString('access-secret', $message);
        $this->assertStringContainsString('[REDACTED]', $message);
    }

    public function test_retention_command_deletes_expired_data_and_preserves_recent_data(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Retention room', 'code' => 'RETENTION', 'slug' => 'retention-room', 'status' => 'active',
        ]);
        $session = LiveSession::query()->create([
            'classroom_id' => $classroom->id, 'teacher_id' => $teacher->id, 'created_by' => $teacher->id,
            'title' => 'Retention session', 'room_name' => 'retention-session', 'provider' => 'native',
            'fallback_provider' => 'native', 'provider_status' => 'created', 'sync_status' => 'not_required',
            'session_type' => 'flexible', 'scheduled_start' => now()->subDays(45), 'scheduled_end' => now()->subDays(45)->addHour(),
            'status' => 'ended',
        ]);
        $expired = LiveSessionMessage::query()->create(['live_session_id' => $session->id, 'sender_id' => $teacher->id, 'body' => 'Expired']);
        $expired->timestamps = false;
        $expired->forceFill(['created_at' => now()->subDays(31), 'updated_at' => now()->subDays(31)])->save();
        $recent = LiveSessionMessage::query()->create(['live_session_id' => $session->id, 'sender_id' => $teacher->id, 'body' => 'Recent']);
        SystemSetting::query()->create(['group' => 'live_session', 'key' => 'live_data_retention_days', 'type' => 'integer', 'value' => '30']);

        $this->artisan('live-sessions:prune-data')->assertSuccessful();

        $this->assertModelMissing($expired);
        $this->assertModelExists($recent);
    }

    private function settings(array $overrides = []): array
    {
        return [...[
            'live_google_meet_enabled' => 1,
            'live_zoom_enabled' => 1,
            'live_max_participants' => 48,
            'live_max_duration_minutes' => 180,
            'live_max_sessions_per_teacher_daily' => 12,
            'live_max_bitrate_kbps' => 1800,
            'live_recording_enabled' => 1,
            'live_recording_max_minutes' => 120,
            'live_data_retention_days' => 180,
            'live_recording_retention_days' => 30,
            'live_recording_consent_required' => 1,
        ], ...$overrides];
    }
}
