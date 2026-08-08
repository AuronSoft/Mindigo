<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherDiscussion\Models\DiscussionMessage;
use Mindigo\TeacherDiscussion\Models\DiscussionParticipant;
use Mindigo\TeacherDiscussion\Models\DiscussionThread;
use Tests\TestCase;

class DiscussionConversationPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_mute_and_pin_own_conversation(): void
    {
        [$student, $thread] = $this->conversation();

        $this->actingAs($student)
            ->patch(route('student.discussions.preferences.update', $thread), [
                'is_muted' => true,
                'is_pinned' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('teacher_discussion_participants', [
            'thread_id' => $thread->id,
            'user_id' => $student->id,
            'is_muted' => true,
            'is_pinned' => true,
        ]);
    }

    public function test_participant_can_pin_message_in_conversation(): void
    {
        [$student, $thread] = $this->conversation();
        $message = DiscussionMessage::query()->create([
            'thread_id' => $thread->id,
            'sender_id' => $student->id,
            'body' => 'Nội dung cần ghi nhớ',
        ]);

        $this->actingAs($student)
            ->patch(route('student.discussions.messages.pin', [$thread, $message]), [
                'is_pinned' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('teacher_discussion_messages', [
            'id' => $message->id,
            'is_pinned' => true,
            'pinned_by' => $student->id,
        ]);
    }

    public function test_participant_cannot_pin_message_from_another_conversation(): void
    {
        [$student, $thread] = $this->conversation();
        [, $otherThread] = $this->conversation();
        $message = DiscussionMessage::query()->create([
            'thread_id' => $otherThread->id,
            'sender_id' => $student->id,
            'body' => 'Tin nhắn ngoài hội thoại',
        ]);

        $this->actingAs($student)
            ->patch(route('student.discussions.messages.pin', [$thread, $message]), [
                'is_pinned' => true,
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('teacher_discussion_messages', [
            'id' => $message->id,
            'is_pinned' => false,
        ]);
    }

    /**
     * @return array{0: User, 1: DiscussionThread}
     */
    private function conversation(): array
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $thread = DiscussionThread::query()->create([
            'teacher_id' => $teacher->id,
            'type' => DiscussionThread::TYPE_DIRECT,
            'created_by' => $teacher->id,
            'last_message_at' => now(),
        ]);

        DiscussionParticipant::query()->create([
            'thread_id' => $thread->id,
            'user_id' => $student->id,
            'role' => DiscussionParticipant::ROLE_MEMBER,
            'joined_at' => now(),
        ]);

        DiscussionParticipant::query()->create([
            'thread_id' => $thread->id,
            'user_id' => $teacher->id,
            'role' => DiscussionParticipant::ROLE_OWNER,
            'joined_at' => now(),
        ]);

        return [$student, $thread];
    }
}
