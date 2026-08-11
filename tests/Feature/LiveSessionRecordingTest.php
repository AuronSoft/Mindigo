<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Enums\LiveParticipantRole;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionParticipant;
use Mindigo\TeacherLiveSession\Models\LiveSessionRecording;
use Mindigo\TeacherLiveSession\Services\LiveSessionJoinTokenService;
use Tests\TestCase;

final class LiveSessionRecordingTest extends TestCase
{
    use RefreshDatabase;

    public function test_moderator_uploads_verified_chunks_finalizes_and_streams_private_recording(): void
    {
        Storage::fake('local');
        [$session, $teacher, $student] = $this->room();
        $token = app(LiveSessionJoinTokenService::class)->issue($session, $teacher, LiveParticipantRole::Host);
        $recordingId = $this->actingAs($teacher)->postJson(route('live-recordings.start', $session), [
            'token' => $token, 'mime_type' => 'video/webm',
        ])->assertCreated()->json('recording_id');
        $chunks = ['first-webm-chunk', 'second-webm-chunk'];
        foreach ($chunks as $sequence => $content) {
            $this->actingAs($teacher)->post(route('live-recordings.chunk', [$session, $recordingId]), [
                'token' => $token, 'sequence' => $sequence, 'checksum' => hash('sha256', $content),
                'chunk' => UploadedFile::fake()->createWithContent('recording.part', $content),
            ])->assertAccepted();
        }
        $response = $this->actingAs($teacher)->postJson(route('live-recordings.finalize', [$session, $recordingId]), [
            'token' => $token, 'duration_seconds' => 42, 'expected_chunks' => 2,
        ])->assertOk()->assertJsonPath('status', 'ready');
        $recording = LiveSessionRecording::query()->findOrFail($recordingId);
        Storage::disk('local')->assertExists($recording->storage_path);
        $this->assertSame(implode('', $chunks), Storage::disk('local')->get($recording->storage_path));
        $this->assertSame(42, $recording->duration_seconds);
        $this->actingAs($teacher)->get($response->json('playback_url'))->assertOk();
        $this->actingAs($student)->get($response->json('playback_url'))->assertOk();
    }

    public function test_recording_requires_consent_and_rejects_corrupt_or_missing_chunks(): void
    {
        Storage::fake('local');
        [$session, $teacher] = $this->room();
        $participant = $session->participants()->where('user_id', $teacher->id)->firstOrFail();
        $participant->update(['recording_consented_at' => null]);
        $token = app(LiveSessionJoinTokenService::class)->issue($session, $teacher, LiveParticipantRole::Host);
        $this->actingAs($teacher)->postJson(route('live-recordings.start', $session), [
            'token' => $token, 'mime_type' => 'video/webm',
        ])->assertUnprocessable();
        $participant->update(['recording_consented_at' => now()]);
        $recordingId = $this->actingAs($teacher)->postJson(route('live-recordings.start', $session), [
            'token' => $token, 'mime_type' => 'video/webm',
        ])->assertCreated()->json('recording_id');
        $this->actingAs($teacher)->post(route('live-recordings.chunk', [$session, $recordingId]), [
            'token' => $token, 'sequence' => 0, 'checksum' => str_repeat('0', 64),
            'chunk' => UploadedFile::fake()->createWithContent('recording.part', 'real-content'),
        ])->assertSessionHasErrors('checksum');
        $this->actingAs($teacher)->postJson(route('live-recordings.finalize', [$session, $recordingId]), [
            'token' => $token, 'duration_seconds' => 10, 'expected_chunks' => 1,
        ])->assertUnprocessable()->assertJsonValidationErrors('chunks');
        $this->actingAs($teacher)->post(route('teacher.live-sessions.end', $session))->assertSessionHasErrors('recording');
        $this->actingAs($teacher)->postJson(route('live-recordings.abort', [$session, $recordingId]), ['token' => $token])->assertAccepted();
        $this->assertDatabaseHas('live_session_recordings', ['id' => $recordingId, 'status' => 'failed']);
    }

    public function test_non_moderator_cannot_record_and_outsider_cannot_watch(): void
    {
        Storage::fake('local');
        [$session, $teacher] = $this->room();
        $outsider = $this->createUser(['role' => 'student']);
        $this->actingAs($outsider)->postJson(route('live-recordings.start', $session), [
            'token' => 'invalid', 'mime_type' => 'video/webm',
        ])->assertForbidden();
        $recording = LiveSessionRecording::query()->create([
            'live_session_id' => $session->id, 'initiated_by' => $teacher->id, 'status' => 'ready',
            'mime_type' => 'video/webm', 'storage_disk' => 'local', 'storage_path' => 'live-recordings/test.webm',
            'size_bytes' => 4, 'duration_seconds' => 1, 'started_at' => now(), 'ended_at' => now(),
        ]);
        Storage::disk('local')->put($recording->storage_path, 'test');
        $this->actingAs($outsider)->get(route('live-recordings.stream', $recording))->assertForbidden();
    }

    private function room(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Recorded class', 'code' => 'REC-'.uniqid(), 'slug' => 'rec-'.uniqid(), 'status' => 'active',
        ]);
        $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);
        $session = LiveSession::query()->create([
            'classroom_id' => $classroom->id, 'teacher_id' => $teacher->id, 'created_by' => $teacher->id,
            'title' => 'Recorded lesson', 'room_name' => 'recording-'.uniqid(), 'provider' => 'native',
            'provider_status' => 'live', 'fallback_provider' => 'native', 'sync_status' => 'not_required',
            'session_type' => 'flexible', 'room_settings' => ['recording_enabled' => true],
            'scheduled_start' => now()->subMinute(), 'scheduled_end' => now()->addHour(), 'status' => 'live',
        ]);
        LiveSessionParticipant::query()->create([
            'live_session_id' => $session->id, 'user_id' => $teacher->id, 'role' => LiveParticipantRole::Host,
            'admission_status' => ParticipantAdmissionStatus::Admitted, 'admitted_at' => now(), 'recording_consented_at' => now(),
        ]);

        return [$session, $teacher, $student];
    }
}
