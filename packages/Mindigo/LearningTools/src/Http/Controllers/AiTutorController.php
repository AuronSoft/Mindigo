<?php

namespace Mindigo\LearningTools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Mindigo\LearningTools\Http\Requests\AiMessageRequest;
use Mindigo\LearningTools\Models\AiConversation;
use Mindigo\LearningTools\Services\MindigoBotService;
use Throwable;

class AiTutorController extends Controller
{
    public function index(Request $request): View
    {
        return view('learning-tools::ai.index', ['conversations' => AiConversation::where('user_id', $request->user()->getAuthIdentifier())->withCount('messages')->latest('updated_at')->paginate(15)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'], 'subject' => ['nullable', 'string', 'max:120'],
            'mode' => ['required', Rule::in(['explain', 'hint', 'quiz', 'review', 'plan'])],
        ]);
        $conversation = AiConversation::create([...$data, 'user_id' => $request->user()->getAuthIdentifier()]);

        return to_route('learning-tools.ai.show', $conversation)->with('success', __('learning-tools::app.ai.created'));
    }

    public function show(Request $request, AiConversation $conversation): View
    {
        $this->authorizeOwner($request, $conversation);
        $conversation->load('messages');

        return view('learning-tools::ai.show', compact('conversation'));
    }

    public function send(AiMessageRequest $request, AiConversation $conversation, MindigoBotService $tutor): RedirectResponse
    {
        $this->authorizeOwner($request, $conversation);
        $message = $request->validated('message');
        $conversation->messages()->create(['role' => 'user', 'content' => $message]);
        $history = $conversation->messages()->latest()->limit(10)->get()->reverse()->map(fn ($item) => ['role' => $item->role, 'content' => $item->content])->all();
        try {
            $reply = $tutor->reply($message, $history, $conversation->subject ?: __('learning-tools::app.ai.general'), $conversation->mode);
            $conversation->messages()->create(['role' => 'assistant', 'content' => $reply['content'], 'provider_response_id' => $reply['response_id'], 'input_tokens' => $reply['input_tokens'], 'output_tokens' => $reply['output_tokens']]);
        } catch (Throwable $exception) {
            report($exception);
            $conversation->messages()->create(['role' => 'assistant', 'content' => __('learning-tools::app.ai.errors.unavailable'), 'status' => 'failed', 'error_message' => $exception->getMessage()]);

            return back()->withErrors(['message' => $exception->getMessage()]);
        }
        $conversation->touch();

        return back();
    }

    public function destroy(Request $request, AiConversation $conversation): RedirectResponse
    {
        $this->authorizeOwner($request, $conversation);
        $conversation->delete();

        return to_route('learning-tools.ai.index')->with('success', __('learning-tools::app.ai.deleted'));
    }

    private function authorizeOwner(Request $request, AiConversation $conversation): void
    {
        abort_unless((int) $conversation->user_id === (int) $request->user()->getAuthIdentifier(), 403);
    }
}
