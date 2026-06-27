<?php

namespace Mindigo\TeacherDiscussion\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherDiscussion\Http\Requests\StoreDiscussionMessageRequest;
use Mindigo\TeacherDiscussion\Models\DiscussionThread;
use Mindigo\TeacherDiscussion\Services\TeacherDiscussionService;

class TeacherDiscussionController extends Controller
{
    public function __construct(private readonly TeacherDiscussionService $service) {}

    public function index()
    {
        session()->forget('url.intended');

        /** @var \Mindigo\Auth\Models\User $teacher */
        $teacher = Auth::user();
        $this->service->ensureThreadsForClassrooms($teacher);

        $threads = $this->service->threads($teacher);
        $selectedThread = $this->service->selectedThread($teacher, request()->integer('thread'));
        $messages = $selectedThread ? $this->service->messages($selectedThread) : collect();
        $members = $selectedThread ? $this->service->members($selectedThread) : collect();

        return view('teacher-discussion::index', compact('teacher', 'threads', 'selectedThread', 'messages', 'members'));
    }

    public function store(StoreDiscussionMessageRequest $request, DiscussionThread $thread): RedirectResponse
    {
        $this->authorizeThread($thread);

        /** @var \Mindigo\Auth\Models\User $teacher */
        $teacher = Auth::user();
        $this->service->send($thread, $teacher, $request->string('body')->toString());

        return redirect()
            ->route('teacher.discussions.index', ['thread' => $thread->id])
            ->with('success', __('teacher-discussion::app.sent'));
    }

    private function authorizeThread(DiscussionThread $thread): void
    {
        /** @var \Mindigo\Auth\Models\User $user */
        $user = Auth::user();

        abort_unless(
            $user instanceof User && ($user->isAdmin() || $thread->teacher_id === (int) $user->getAuthIdentifier()),
            403
        );
    }
}
