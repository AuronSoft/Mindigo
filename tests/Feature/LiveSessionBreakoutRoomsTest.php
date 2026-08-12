<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Enums\LiveParticipantRole;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionBreakoutRoom;
use Mindigo\TeacherLiveSession\Models\LiveSessionParticipant;
use Mindigo\TeacherLiveSession\Services\LiveSessionJoinTokenService;
use Tests\TestCase;

final class LiveSessionBreakoutRoomsTest extends TestCase
{
    use RefreshDatabase;

    public function test_moderator_creates_balanced_rooms_and_students_cannot_manage_them(): void
    {
        [$session, $teacher, $students] = $this->room();
        $student = $students->first();

        $this->actingAs($student)->postJson(route('live-breakouts.store', $session), [
            'token' => $this->token($session, $student, LiveParticipantRole::Student),
            'room_count' => 2, 'duration_minutes' => 15, 'auto_assign' => true,
        ])->assertForbidden();

        $this->actingAs($teacher)->postJson(route('live-breakouts.store', $session), [
            'token' => $this->token($session, $teacher, LiveParticipantRole::Host),
            'room_count' => 2, 'duration_minutes' => 15, 'auto_assign' => true,
        ])->assertCreated()->assertJsonCount(2, 'room_ids');

        $this->assertSame(2, LiveSessionBreakoutRoom::query()->count());
        $this->assertSame([2, 2], LiveSessionBreakoutRoom::query()->withCount('assignments')->orderBy('id')->pluck('assignments_count')->all());
    }

    public function test_open_rooms_isolates_media_and_close_returns_everyone_to_main_room(): void
    {
        [$session, $teacher, $students] = $this->room();
        $teacherToken = $this->token($session, $teacher, LiveParticipantRole::Host);
        $this->actingAs($teacher)->postJson(route('live-breakouts.store', $session), [
            'token' => $teacherToken, 'room_count' => 2, 'duration_minutes' => 15, 'auto_assign' => true,
        ])->assertCreated();
        $this->actingAs($teacher)->postJson(route('live-breakouts.open', $session), ['token' => $teacherToken])->assertAccepted();

        $first = $students[0];
        $second = $students[1];
        $firstParticipant = LiveSessionParticipant::query()->where('user_id', $first->id)->firstOrFail();
        $secondParticipant = LiveSessionParticipant::query()->where('user_id', $second->id)->firstOrFail();
        $this->assertNotSame($firstParticipant->breakout_room_id, $secondParticipant->breakout_room_id);

        $presence = $this->actingAs($first)->postJson(route('live-media.presence', $session), [
            'token' => $this->token($session, $first, LiveParticipantRole::Student), 'connection_id' => 'first-browser',
        ])->assertOk();
        $presence->assertJsonCount(2, 'participants');
        $this->actingAs($first)->postJson(route('live-media.signals.store', $session), [
            'token' => $this->token($session, $first, LiveParticipantRole::Student),
            'recipient_key' => 'user:'.$second->id, 'type' => 'offer', 'payload' => ['type' => 'offer', 'sdp' => 'isolated'],
        ])->assertUnprocessable();

        $this->actingAs($teacher)->postJson(route('live-breakouts.close', $session), ['token' => $teacherToken])->assertAccepted();
        $this->assertDatabaseMissing('live_session_participants', ['live_session_id' => $session->id, 'breakout_room_id' => $firstParticipant->breakout_room_id]);
        $this->assertDatabaseCount('live_session_breakout_rooms', 2);
        $this->assertSame(2, LiveSessionBreakoutRoom::query()->where('status', 'closed')->count());
    }

    private function room(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $students = collect(range(1, 4))->map(fn () => $this->createUser(['role' => 'student']));
        $classroom = Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE, 'name' => 'Breakout class', 'code' => 'BR-'.uniqid(), 'slug' => 'br-'.uniqid(), 'status' => 'active']);
        $students->each(fn ($student) => $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]));
        $session = LiveSession::query()->create(['classroom_id' => $classroom->id, 'teacher_id' => $teacher->id, 'created_by' => $teacher->id, 'title' => 'Group lesson', 'room_name' => 'group-'.uniqid(), 'provider' => 'native', 'provider_status' => 'live', 'fallback_provider' => 'native', 'sync_status' => 'not_required', 'session_type' => 'flexible', 'scheduled_start' => now()->subMinute(), 'scheduled_end' => now()->addHour(), 'status' => 'live']);
        LiveSessionParticipant::query()->create(['live_session_id' => $session->id, 'user_id' => $teacher->id, 'role' => LiveParticipantRole::Host, 'admission_status' => ParticipantAdmissionStatus::Admitted, 'admitted_at' => now(), 'last_seen_at' => now()]);
        $students->each(fn ($student) => LiveSessionParticipant::query()->create(['live_session_id' => $session->id, 'user_id' => $student->id, 'role' => LiveParticipantRole::Student, 'admission_status' => ParticipantAdmissionStatus::Admitted, 'admitted_at' => now(), 'last_seen_at' => now()]));

        return [$session, $teacher, $students];
    }

    private function token(LiveSession $session, $user, LiveParticipantRole $role): string
    {
        return app(LiveSessionJoinTokenService::class)->issue($session, $user, $role);
    }
}
