<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Enums\LiveParticipantRole;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionParticipant;
use Tests\TestCase;

final class LiveSessionPhaseTwentyFourTest extends TestCase
{
    use RefreshDatabase;

    public function test_native_room_renders_accessible_prejoin_device_and_caption_controls(): void
    {
        [$session, $teacher] = $this->room();

        $response = $this->actingAs($teacher)->get(route('teacher.live-sessions.room', $session));

        $response->assertOk()
            ->assertSee('data-prejoin', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('data-prejoin-camera', false)
            ->assertSee('data-prejoin-microphone', false)
            ->assertSee('data-test-speaker', false)
            ->assertSee('data-prejoin-background', false)
            ->assertSee('data-toggle-captions', false)
            ->assertSee('aria-live="polite"', false);
    }

    private function room(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE, 'name' => 'Accessible live room', 'code' => 'P24-'.str()->random(6), 'slug' => 'p24-'.str()->random(8), 'status' => 'active']);
        $session = LiveSession::query()->create(['classroom_id' => $classroom->id, 'teacher_id' => $teacher->id, 'created_by' => $teacher->id, 'title' => 'Phase 24', 'room_name' => 'phase-24-room', 'provider' => 'native', 'provider_status' => 'live', 'fallback_provider' => 'native', 'sync_status' => 'not_required', 'session_type' => 'flexible', 'scheduled_start' => now()->subMinute(), 'scheduled_end' => now()->addHour(), 'status' => 'live']);
        LiveSessionParticipant::query()->create(['live_session_id' => $session->id, 'user_id' => $teacher->id, 'role' => LiveParticipantRole::Host, 'admission_status' => ParticipantAdmissionStatus::Admitted, 'admitted_at' => now()]);

        return [$session, $teacher];
    }
}
