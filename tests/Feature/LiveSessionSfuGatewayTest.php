<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Enums\LiveParticipantRole;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionParticipant;
use Mindigo\TeacherLiveSession\Services\LiveSessionJoinTokenService;
use Tests\TestCase;

final class LiveSessionSfuGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_admitted_participant_receives_short_lived_signed_sfu_ticket(): void
    {
        config()->set('live-media.topology', 'sfu');
        config()->set('live-media.gateway.secret', str_repeat('sfu-secret-', 4));
        config()->set('live-media.gateway.public_url', 'ws://127.0.0.1:8090');
        $teacher = $this->createUser(['role' => 'teacher']);
        $session = $this->liveSession($teacher->id);
        LiveSessionParticipant::query()->create([
            'live_session_id' => $session->id,
            'user_id' => $teacher->id,
            'role' => LiveParticipantRole::Host,
            'admission_status' => ParticipantAdmissionStatus::Admitted,
            'admitted_at' => now(),
        ]);
        $joinToken = app(LiveSessionJoinTokenService::class)->issue($session, $teacher, LiveParticipantRole::Host);

        $response = $this->actingAs($teacher)->postJson(route('live-media.gateway-ticket', $session), ['token' => $joinToken]);

        $response->assertOk()
            ->assertJsonPath('topology', 'sfu')
            ->assertJsonPath('gateway_url', 'ws://127.0.0.1:8090')
            ->assertJsonStructure(['ticket', 'expires_in']);
        [$encodedClaims, $signature] = explode('.', $response->json('ticket'));
        $claims = json_decode(base64_decode(strtr($encodedClaims, '-_', '+/')), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame($session->id, $claims['session_id']);
        $this->assertSame('user:'.$teacher->id, $claims['participant_key']);
        $this->assertSame('host', $claims['role']);
        $this->assertLessThanOrEqual(120, $claims['exp'] - $claims['iat']);
        $this->assertNotSame('', $signature);
    }

    public function test_gateway_ticket_is_disabled_for_mesh_and_denied_for_waiting_participant(): void
    {
        config()->set('live-media.gateway.secret', str_repeat('sfu-secret-', 4));
        $student = $this->createUser(['role' => 'student']);
        $session = $this->liveSession($student->id);
        LiveSessionParticipant::query()->create([
            'live_session_id' => $session->id,
            'user_id' => $student->id,
            'role' => LiveParticipantRole::Student,
            'admission_status' => ParticipantAdmissionStatus::Waiting,
        ]);
        $token = app(LiveSessionJoinTokenService::class)->issue($session, $student, LiveParticipantRole::Student);

        $this->actingAs($student)->postJson(route('live-media.gateway-ticket', $session), ['token' => $token])->assertConflict();
        config()->set('live-media.topology', 'sfu');
        $this->actingAs($student)->postJson(route('live-media.gateway-ticket', $session), ['token' => $token])->assertNotFound();
    }

    private function liveSession(int $teacherId): LiveSession
    {
        $classroom = Classroom::query()->create([
            'created_by' => $teacherId,
            'teacher_id' => $teacherId,
            'type' => Classroom::TYPE_STANDALONE,
            'name' => 'SFU class',
            'code' => 'SFU-'.uniqid(),
            'slug' => 'sfu-'.uniqid(),
            'status' => 'active',
        ]);

        return LiveSession::query()->create([
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacherId,
            'created_by' => $teacherId,
            'title' => 'SFU room',
            'room_name' => 'sfu-'.uniqid(),
            'provider' => 'native',
            'provider_status' => 'live',
            'fallback_provider' => 'native',
            'sync_status' => 'not_required',
            'session_type' => 'flexible',
            'scheduled_start' => now()->subMinute(),
            'scheduled_end' => now()->addHour(),
            'status' => 'live',
        ]);
    }
}
