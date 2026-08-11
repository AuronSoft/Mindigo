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

final class LiveSessionNativeMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admitted_users_exchange_scoped_signals_and_report_presence(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $session = $this->liveSession($teacher->id);
        $this->participant($session, $teacher->id, LiveParticipantRole::Host);
        $this->participant($session, $student->id, LiveParticipantRole::Student);
        $tokens = app(LiveSessionJoinTokenService::class);
        $teacherToken = $tokens->issue($session, $teacher, LiveParticipantRole::Host);
        $studentToken = $tokens->issue($session, $student, LiveParticipantRole::Student);

        $this->actingAs($teacher)->postJson(route('live-media.presence', $session), [
            'token' => $teacherToken,
            'connection_id' => 'teacher-browser',
            'microphone_enabled' => true,
        ])->assertOk()->assertJsonPath('participants.0.user_id', $teacher->id);

        $this->actingAs($teacher)->postJson(route('live-media.signals.store', $session), [
            'token' => $teacherToken,
            'recipient_id' => $student->id,
            'type' => 'offer',
            'payload' => ['type' => 'offer', 'sdp' => 'safe-test-sdp'],
        ])->assertAccepted();

        $this->actingAs($student)->postJson(route('live-media.signals.inbox', $session), ['token' => $studentToken])
            ->assertOk()->assertJsonPath('signals.0.sender_id', $teacher->id)->assertJsonPath('signals.0.type', 'offer');
        $this->actingAs($student)->postJson(route('live-media.signals.inbox', $session), ['token' => $studentToken])
            ->assertOk()->assertJsonCount(0, 'signals');
    }

    public function test_media_api_rejects_revoked_token_and_non_admitted_recipient(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $session = $this->liveSession($teacher->id);
        $this->participant($session, $teacher->id, LiveParticipantRole::Host);
        $token = app(LiveSessionJoinTokenService::class)->issue($session, $teacher, LiveParticipantRole::Host);

        $this->actingAs($teacher)->postJson(route('live-media.signals.store', $session), [
            'token' => $token,
            'recipient_id' => $student->id,
            'type' => 'offer',
            'payload' => ['type' => 'offer', 'sdp' => 'test'],
        ])->assertUnprocessable();

        $session->increment('join_token_version');
        $this->actingAs($teacher)->postJson(route('live-media.presence', $session), [
            'token' => $token,
            'connection_id' => 'stale-browser',
        ])->assertUnprocessable()->assertJsonValidationErrors('join_token');
    }

    private function liveSession(int $teacherId): LiveSession
    {
        $classroom = Classroom::query()->create([
            'created_by' => $teacherId, 'teacher_id' => $teacherId, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Media class', 'code' => 'MED-'.uniqid(), 'slug' => 'media-'.uniqid(), 'status' => 'active',
        ]);

        return LiveSession::query()->create([
            'classroom_id' => $classroom->id, 'teacher_id' => $teacherId, 'created_by' => $teacherId,
            'title' => 'Native media room', 'room_name' => 'media-'.uniqid(), 'provider' => 'native',
            'provider_status' => 'live', 'fallback_provider' => 'native', 'sync_status' => 'not_required',
            'session_type' => 'flexible', 'scheduled_start' => now()->subMinute(),
            'scheduled_end' => now()->addHour(), 'status' => 'live',
        ]);
    }

    private function participant(LiveSession $session, int $userId, LiveParticipantRole $role): void
    {
        LiveSessionParticipant::query()->create([
            'live_session_id' => $session->id, 'user_id' => $userId, 'role' => $role,
            'admission_status' => ParticipantAdmissionStatus::Admitted, 'admitted_at' => now(),
        ]);
    }
}
