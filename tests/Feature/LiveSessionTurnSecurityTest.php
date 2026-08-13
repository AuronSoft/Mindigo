<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Enums\LiveParticipantRole;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionParticipant;
use Mindigo\TeacherLiveSession\Services\LiveSessionJoinTokenService;
use Mindigo\TeacherLiveSession\Services\TurnCredentialService;
use Mindigo\TeacherLiveSession\Services\TurnServerHealthService;
use Tests\TestCase;

final class LiveSessionTurnSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget(TurnServerHealthService::CACHE_KEY);
        config()->set('live-media.turn.auth_secret', str_repeat('turn-secret-', 4));
        config()->set('live-media.turn.credential_ttl_seconds', 300);
        config()->set('live-media.turn.max_bitrate_kbps', 1800);
        config()->set('live-media.static_ice_servers', [['urls' => ['stun:stun.test:3478']]]);
        config()->set('live-media.turn.nodes', [[
            'id' => 'primary', 'urls' => ['turn:turn.test:3478?transport=udp'], 'health_url' => null,
        ]]);
    }

    public function test_turn_credentials_are_short_lived_scoped_and_rotated(): void
    {
        $first = app(TurnCredentialService::class)->issue(21, 'user:7');
        $second = app(TurnCredentialService::class)->issue(21, 'user:7');
        $turn = $first['ice_servers'][1];
        [$expiresAt, $sessionId, $participantType, $participantId] = explode(':', $turn['username'], 5);

        $this->assertSame('21', $sessionId);
        $this->assertSame('user', $participantType);
        $this->assertSame('7', $participantId);
        $this->assertLessThanOrEqual(300, (int) $expiresAt - now()->timestamp);
        $this->assertSame(base64_encode(hash_hmac('sha1', $turn['username'], str_repeat('turn-secret-', 4), true)), $turn['credential']);
        $this->assertNotSame($first['ice_servers'][1]['username'], $second['ice_servers'][1]['username']);
        $this->assertSame(1800, $first['max_bitrate_kbps']);
    }

    public function test_health_registry_removes_failed_primary_and_uses_failover(): void
    {
        config()->set('live-media.turn.nodes', [
            ['id' => 'primary', 'urls' => ['turn:primary.test:3478'], 'health_url' => 'https://primary.test/health'],
            ['id' => 'failover', 'urls' => ['turn:failover.test:3478'], 'health_url' => 'https://failover.test/health'],
        ]);
        Http::fake([
            'primary.test/*' => Http::response([], 503),
            'failover.test/*' => Http::response(['status' => 'ok']),
        ]);

        $states = app(TurnServerHealthService::class)->refresh();
        $issued = app(TurnCredentialService::class)->issue(9, 'guest:2');

        $this->assertFalse($states['primary']['healthy']);
        $this->assertTrue($states['failover']['healthy']);
        $this->assertSame(['turn:failover.test:3478'], $issued['ice_servers'][1]['urls']);
    }

    public function test_only_admitted_participant_can_request_dynamic_ice_servers(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $session = $this->liveSession($teacher->id);
        $participant = LiveSessionParticipant::query()->create([
            'live_session_id' => $session->id,
            'user_id' => $teacher->id,
            'role' => LiveParticipantRole::Host,
            'admission_status' => ParticipantAdmissionStatus::Admitted,
            'admitted_at' => now(),
        ]);
        $token = app(LiveSessionJoinTokenService::class)->issue($session, $teacher, LiveParticipantRole::Host);

        $this->actingAs($teacher)->postJson(route('live-media.ice-servers', $session), ['token' => $token])
            ->assertOk()->assertJsonPath('turn_available', true)->assertJsonPath('expires_in', 300)
            ->assertJsonMissingPath('auth_secret');

        $participant->update(['admission_status' => ParticipantAdmissionStatus::Removed]);
        $this->actingAs($teacher)->postJson(route('live-media.ice-servers', $session), ['token' => $token])->assertNotFound();
    }

    private function liveSession(int $teacherId): LiveSession
    {
        $classroom = Classroom::query()->create([
            'created_by' => $teacherId, 'teacher_id' => $teacherId, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'TURN class', 'code' => 'TURN-'.uniqid(), 'slug' => 'turn-'.uniqid(), 'status' => 'active',
        ]);

        return LiveSession::query()->create([
            'classroom_id' => $classroom->id, 'teacher_id' => $teacherId, 'created_by' => $teacherId,
            'title' => 'TURN room', 'room_name' => 'turn-'.uniqid(), 'provider' => 'native',
            'provider_status' => 'live', 'fallback_provider' => 'native', 'sync_status' => 'not_required',
            'session_type' => 'flexible', 'scheduled_start' => now()->subMinute(),
            'scheduled_end' => now()->addHour(), 'status' => 'live',
        ]);
    }
}
