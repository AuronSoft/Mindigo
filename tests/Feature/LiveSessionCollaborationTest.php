<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\AuditLog\Models\AuditLog;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Enums\LiveParticipantRole;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionParticipant;
use Mindigo\TeacherLiveSession\Services\LiveSessionJoinTokenService;
use Tests\TestCase;

final class LiveSessionCollaborationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admitted_participants_chat_raise_hands_and_send_reactions(): void
    {
        [$session, $teacher, $student] = $this->room();
        $studentToken = $this->token($session, $student, LiveParticipantRole::Student);

        $this->actingAs($student)->postJson(route('live-collaboration.messages.store', $session), [
            'token' => $studentToken, 'body' => '  Em đã hiểu bài.  ',
        ])->assertCreated();
        $this->actingAs($student)->postJson(route('live-collaboration.actions.store', $session), [
            'token' => $studentToken, 'action' => 'raise_hand',
        ])->assertAccepted();
        $this->actingAs($student)->postJson(route('live-collaboration.actions.store', $session), [
            'token' => $studentToken, 'action' => 'reaction', 'reaction' => 'clap',
        ])->assertAccepted();

        $response = $this->actingAs($teacher)->postJson(route('live-collaboration.sync', $session), [
            'token' => $this->token($session, $teacher, LiveParticipantRole::Host),
            'after_message_id' => 0, 'after_event_id' => 0,
        ])->assertOk();
        $response->assertJsonPath('messages.0.body', 'Em đã hiểu bài.')
            ->assertJsonPath('events.0.payload.reaction', 'clap')
            ->assertJsonPath('participants.0.user_id', $student->id)
            ->assertJsonPath('participants.0.hand_raised', true)
            ->assertJsonPath('can_moderate', true);
    }

    public function test_only_moderator_can_control_student_and_action_is_audited(): void
    {
        [$session, $teacher, $student] = $this->room();
        $teacherToken = $this->token($session, $teacher, LiveParticipantRole::Host);
        $studentToken = $this->token($session, $student, LiveParticipantRole::Student);

        $this->actingAs($student)->postJson(route('live-collaboration.moderate', $session), [
            'token' => $studentToken, 'target_user_id' => $teacher->id, 'action' => 'mute',
        ])->assertForbidden();
        $this->actingAs($teacher)->postJson(route('live-collaboration.moderate', $session), [
            'token' => $teacherToken, 'target_user_id' => $student->id, 'action' => 'mute',
        ])->assertAccepted();

        $this->assertNotNull(LiveSessionParticipant::query()->where('user_id', $student->id)->value('force_muted_at'));
        $this->assertDatabaseHas('live_session_room_events', ['target_user_id' => $student->id, 'type' => 'mute']);
        $this->assertTrue(AuditLog::query()->where('module', 'teacher_live_session')->where('action', 'participant_mute')->exists());

        $this->actingAs($teacher)->postJson(route('live-collaboration.moderate', $session), [
            'token' => $teacherToken, 'target_user_id' => $student->id, 'action' => 'allow_microphone',
        ])->assertAccepted();
        $this->assertNull(LiveSessionParticipant::query()->where('user_id', $student->id)->value('force_muted_at'));
    }

    public function test_chat_can_be_disabled_and_data_never_crosses_rooms(): void
    {
        [$session, $teacher, $student] = $this->room(['room_settings' => ['chat_enabled' => false]]);
        $this->actingAs($student)->postJson(route('live-collaboration.messages.store', $session), [
            'token' => $this->token($session, $student, LiveParticipantRole::Student), 'body' => 'Blocked',
        ])->assertForbidden();

        [$otherSession, $otherTeacher] = $this->room();
        $response = $this->actingAs($otherTeacher)->postJson(route('live-collaboration.sync', $otherSession), [
            'token' => $this->token($otherSession, $otherTeacher, LiveParticipantRole::Host),
        ])->assertOk();
        $response->assertJsonCount(0, 'messages')->assertJsonCount(0, 'events');
    }

    private function room(array $sessionAttributes = []): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Collaboration class', 'code' => 'COL-'.uniqid(), 'slug' => 'col-'.uniqid(), 'status' => 'active',
        ]);
        $session = LiveSession::query()->create([
            'classroom_id' => $classroom->id, 'teacher_id' => $teacher->id, 'created_by' => $teacher->id,
            'title' => 'Interactive lesson', 'room_name' => 'collab-'.uniqid(), 'provider' => 'native',
            'provider_status' => 'live', 'fallback_provider' => 'native', 'sync_status' => 'not_required',
            'session_type' => 'flexible', 'scheduled_start' => now()->subMinute(),
            'scheduled_end' => now()->addHour(), 'status' => 'live', ...$sessionAttributes,
        ]);
        foreach ([[$teacher, LiveParticipantRole::Host], [$student, LiveParticipantRole::Student]] as [$user, $role]) {
            LiveSessionParticipant::query()->create([
                'live_session_id' => $session->id, 'user_id' => $user->id, 'role' => $role,
                'admission_status' => ParticipantAdmissionStatus::Admitted, 'admitted_at' => now(), 'last_seen_at' => now(),
            ]);
        }

        return [$session, $teacher, $student];
    }

    private function token(LiveSession $session, $user, LiveParticipantRole $role): string
    {
        return app(LiveSessionJoinTokenService::class)->issue($session, $user, $role);
    }
}
