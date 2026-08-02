<?php

namespace Mindigo\TeacherDiscussion\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherDiscussion\Http\Requests\StoreDiscussionMessageRequest;
use Mindigo\TeacherDiscussion\Models\DiscussionAttachment;
use Mindigo\TeacherDiscussion\Models\DiscussionThread;
use Mindigo\TeacherDiscussion\Services\TeacherDiscussionService;

class TeacherDiscussionController extends Controller
{
    public function __construct(private readonly TeacherDiscussionService $service) {}

    public function index()
    {
        session()->forget('url.intended');

        /** @var User $teacher */
        $teacher = Auth::user();
        $this->service->ensureThreadsForClassrooms($teacher);

        $threads = $this->service->threads($teacher);
        $selectedThread = $this->service->selectedThread($teacher, request()->integer('thread'));
        $messages = $selectedThread ? $this->service->messages($selectedThread) : collect();
        $members = $selectedThread ? $this->service->members($selectedThread) : collect();
        $attachments = $selectedThread ? $this->service->attachments($selectedThread) : collect();

        return view('teacher-discussion::index', compact('teacher', 'threads', 'selectedThread', 'messages', 'members', 'attachments'));
    }

    public function store(StoreDiscussionMessageRequest $request, DiscussionThread $thread): RedirectResponse
    {
        $this->authorizeThread($thread);

        /** @var User $teacher */
        $teacher = Auth::user();
        $this->service->send($thread, $teacher, $request->input('body'), $request->file('attachments', []));

        return redirect()
            ->route('teacher.discussions.index', ['thread' => $thread->id])
            ->with('success', __('teacher-discussion::app.sent'));
    }

    public function attachment(DiscussionAttachment $attachment)
    {
        $attachment->loadMissing('message.thread');
        $thread = $attachment->message?->thread;

        abort_unless($thread instanceof DiscussionThread, 404);
        $this->authorizeThread($thread);

        $disk = $attachment->disk ?: 'public';
        $storage = Storage::disk($disk);

        abort_unless($storage->exists($attachment->path), 404);

        $filename = str_replace(['"', "\r", "\n"], '', $attachment->original_name);

        return response()->file($storage->path($attachment->path), [
            'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    private function authorizeThread(DiscussionThread $thread): void
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless(
            $user instanceof User && ($user->isAdmin() || $thread->teacher_id === (int) $user->getAuthIdentifier()),
            403
        );
    }
}
