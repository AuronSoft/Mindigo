<?php

namespace Mindigo\StudentDiscussion\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mindigo\Auth\Models\User;
use Mindigo\StudentDiscussion\Http\Requests\StoreDiscussionMessageRequest;
use Mindigo\StudentDiscussion\Services\DiscussionService;
use Mindigo\TeacherDiscussion\Models\DiscussionAttachment;
use Mindigo\TeacherDiscussion\Models\DiscussionThread;

class DiscussionController extends Controller
{
    public function __construct(protected DiscussionService $service) {}

    public function index(Request $request)
    {
        session()->forget('url.intended');

        /** @var User $student */
        $student = Auth::user();

        $threads = $this->service->threads($student);
        $selectedThread = $this->service->selectedThread($student, $request->integer('thread') ?: null);
        $messages = $selectedThread ? $this->service->messages($selectedThread) : collect();
        $members = $selectedThread ? $this->service->members($selectedThread) : collect();
        $attachments = $selectedThread ? $this->service->attachments($selectedThread) : collect();

        return view('student-discussion::index', compact('student', 'threads', 'selectedThread', 'messages', 'members', 'attachments'));
    }

    public function store(StoreDiscussionMessageRequest $request, DiscussionThread $thread): RedirectResponse
    {
        /** @var User $student */
        $student = Auth::user();

        abort_unless($this->service->canAccess($thread, $student->getAuthIdentifier()), 403);

        $this->service->send($thread, $student, $request->input('body'), $request->file('attachments', []));

        return redirect()
            ->route('student.discussions.index', ['thread' => $thread->id])
            ->with('success', __('student-discussion::app.sent'));
    }

    public function attachment(DiscussionAttachment $attachment)
    {
        $attachment->loadMissing('message.thread');
        $thread = $attachment->message?->thread;

        abort_unless($thread instanceof DiscussionThread, 404);

        /** @var User $student */
        $student = Auth::user();
        abort_unless($this->service->canAccess($thread, $student->getAuthIdentifier()), 403);

        $disk = $attachment->disk ?: 'public';
        $storage = Storage::disk($disk);

        abort_unless($storage->exists($attachment->path), 404);

        $filename = str_replace(['"', "\r", "\n"], '', $attachment->original_name);

        return response()->file($storage->path($attachment->path), [
            'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
