<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mindigo\LearningTools\Models\AiConversation;
use Tests\TestCase;

class LearningToolsPhaseFiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_create_conversation_and_receive_mindigobot_reply(): void
    {
        config(['services.mindigobot.key' => 'test-key', 'services.mindigobot.keys' => ['test-key'], 'services.mindigobot.model' => 'provider-test']);
        Http::fake(['*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'A guided learning response.']]]]],
            'usageMetadata' => ['promptTokenCount' => 12, 'candidatesTokenCount' => 8],
        ])]);
        $student = $this->createUser(['role' => 'student']);

        $this->actingAs($student)->post(route('learning-tools.ai.store'), ['title' => 'Physics tutor', 'subject' => 'Physics', 'mode' => 'explain'])->assertRedirect();
        $conversation = AiConversation::where('user_id', $student->id)->sole();
        $this->actingAs($student)->post(route('learning-tools.ai.send', $conversation), ['message' => 'Explain Newton laws'])->assertRedirect();

        $this->assertDatabaseHas('learning_ai_messages', ['conversation_id' => $conversation->id, 'role' => 'assistant', 'content' => 'A guided learning response.', 'input_tokens' => 12, 'output_tokens' => 8]);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'provider-test:generateContent') && $request['contents'][0]['role'] === 'user');
    }

    public function test_ai_conversations_are_private_to_their_owner(): void
    {
        $owner = $this->createUser(['role' => 'student']);
        $outsider = $this->createUser(['role' => 'student']);
        $conversation = AiConversation::create(['user_id' => $owner->id, 'title' => 'Private tutor', 'mode' => 'hint']);

        $this->actingAs($outsider)->get(route('learning-tools.ai.show', $conversation))->assertForbidden();
        $this->actingAs($outsider)->delete(route('learning-tools.ai.destroy', $conversation))->assertForbidden();
    }

    public function test_missing_mindigobot_configuration_is_handled_safely(): void
    {
        config(['services.mindigobot.key' => null, 'services.mindigobot.keys' => []]);
        $student = $this->createUser(['role' => 'student']);
        $conversation = AiConversation::create(['user_id' => $student->id, 'title' => 'Tutor', 'mode' => 'review']);

        $this->actingAs($student)->post(route('learning-tools.ai.send', $conversation), ['message' => 'Help me review'])->assertRedirect()->assertSessionHasErrors('message');
        $this->assertDatabaseHas('learning_ai_messages', ['conversation_id' => $conversation->id, 'role' => 'assistant', 'status' => 'failed']);
        Http::assertNothingSent();
    }

    public function test_phase_five_pages_require_login_and_render_for_supported_roles(): void
    {
        $this->get(route('learning-tools.ai.index'))->assertRedirect();
        foreach (['student', 'teacher'] as $role) {
            $this->actingAs($this->createUser(['role' => $role]))->get(route('learning-tools.ai.index'))->assertOk();
        }
    }
}
