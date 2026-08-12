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
use Mindigo\TeacherLiveSession\Models\LiveSessionPoll;
use Mindigo\TeacherLiveSession\Models\LiveSessionResource;
use Mindigo\TeacherLiveSession\Services\LiveSessionJoinTokenService;
use Tests\TestCase;

final class LiveSessionTeachingToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_participants_collaborate_on_versioned_whiteboard_and_only_moderator_clears_it(): void
    {
        [$session, $teacher, $student] = $this->room();
        $stroke = ['type' => 'stroke', 'payload' => ['color' => '#16a34a', 'width' => 3, 'points' => [['x' => .1, 'y' => .2], ['x' => .8, 'y' => .9]]]];
        $this->actingAs($student)->postJson(route('live-teaching-tools.whiteboard', $session), ['token' => $this->token($session, $student, LiveParticipantRole::Student), ...$stroke])->assertCreated();
        $this->actingAs($student)->postJson(route('live-teaching-tools.whiteboard', $session), ['token' => $this->token($session, $student, LiveParticipantRole::Student), 'type' => 'clear'])->assertForbidden();
        $this->actingAs($teacher)->postJson(route('live-teaching-tools.whiteboard', $session), ['token' => $this->token($session, $teacher, LiveParticipantRole::Host), 'type' => 'clear'])->assertCreated();
        $this->actingAs($student)->postJson(route('live-teaching-tools.sync', $session), ['token' => $this->token($session, $student, LiveParticipantRole::Student), 'after_action_id' => 0])->assertOk()->assertJsonCount(2, 'actions')->assertJsonPath('actions.1.type', 'clear');
    }

    public function test_moderator_launches_poll_student_votes_once_and_results_are_hidden_until_closed(): void
    {
        [$session, $teacher, $student] = $this->room();
        $teacherToken = $this->token($session, $teacher, LiveParticipantRole::Host);
        $studentToken = $this->token($session, $student, LiveParticipantRole::Student);
        $pollId = $this->actingAs($teacher)->postJson(route('live-teaching-tools.polls.store', $session), ['token' => $teacherToken, 'question' => 'Bạn đã hiểu bài?', 'options' => ['Đã hiểu', 'Cần giải thích thêm']])->assertCreated()->json('poll_id');
        $this->actingAs($teacher)->postJson(route('live-teaching-tools.polls.store', $session), ['token' => $teacherToken, 'question' => 'Trùng?', 'options' => ['Có', ' có ']])->assertUnprocessable()->assertJsonValidationErrors('options');
        $poll = LiveSessionPoll::query()->with('options')->findOrFail($pollId);
        $option = $poll->options->first();
        $this->actingAs($student)->postJson(route('live-teaching-tools.polls.vote', [$session, $poll]), ['token' => $studentToken, 'option_id' => $option->id])->assertAccepted();
        $this->actingAs($student)->postJson(route('live-teaching-tools.polls.vote', [$session, $poll]), ['token' => $studentToken, 'option_id' => $option->id])->assertUnprocessable();
        $this->actingAs($student)->postJson(route('live-teaching-tools.sync', $session), ['token' => $studentToken])->assertJsonPath('poll.options.0.votes', null);
        $this->actingAs($teacher)->postJson(route('live-teaching-tools.polls.close', [$session, $poll]), ['token' => $teacherToken, 'show_results' => true])->assertAccepted();
        $this->actingAs($student)->postJson(route('live-teaching-tools.sync', $session), ['token' => $studentToken])->assertJsonPath('poll.options.0.votes', 1);
    }

    public function test_only_moderator_uploads_private_resource_and_class_member_downloads_it(): void
    {
        Storage::fake('local');
        [$session, $teacher, $student] = $this->room();
        $outsider = $this->createUser(['role' => 'student']);
        $this->actingAs($student)->post(route('live-teaching-tools.resources.store', $session), ['token' => $this->token($session, $student, LiveParticipantRole::Student), 'file' => UploadedFile::fake()->create('lesson.pdf', 10, 'application/pdf')])->assertForbidden();
        $this->actingAs($teacher)->post(route('live-teaching-tools.resources.store', $session), ['token' => $this->token($session, $teacher, LiveParticipantRole::Host), 'file' => UploadedFile::fake()->create('lesson.pdf', 10, 'application/pdf')])->assertCreated();
        $resource = LiveSessionResource::query()->sole();
        Storage::disk('local')->assertExists($resource->storage_path);
        $this->actingAs($student)->get(route('live-teaching-tools.resources.download', $resource))->assertOk();
        $this->actingAs($outsider)->get(route('live-teaching-tools.resources.download', $resource))->assertForbidden();
    }

    private function room(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE, 'name' => 'Tools class', 'code' => 'TLS-'.uniqid(), 'slug' => 'tls-'.uniqid(), 'status' => 'active']);
        $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);
        $session = LiveSession::query()->create(['classroom_id' => $classroom->id, 'teacher_id' => $teacher->id, 'created_by' => $teacher->id, 'title' => 'Tools lesson', 'room_name' => 'tools-'.uniqid(), 'provider' => 'native', 'provider_status' => 'live', 'fallback_provider' => 'native', 'sync_status' => 'not_required', 'session_type' => 'flexible', 'scheduled_start' => now()->subMinute(), 'scheduled_end' => now()->addHour(), 'status' => 'live']);
        foreach ([[$teacher, LiveParticipantRole::Host], [$student, LiveParticipantRole::Student]] as [$user, $role]) {
            LiveSessionParticipant::query()->create(['live_session_id' => $session->id, 'user_id' => $user->id, 'role' => $role, 'admission_status' => ParticipantAdmissionStatus::Admitted, 'admitted_at' => now()]);
        }

        return [$session, $teacher, $student];
    }

    private function token(LiveSession $session, $user, LiveParticipantRole $role): string
    {
        return app(LiveSessionJoinTokenService::class)->issue($session, $user, $role);
    }
}
