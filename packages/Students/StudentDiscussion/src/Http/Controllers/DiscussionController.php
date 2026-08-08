<?php

namespace Mindigo\StudentDiscussion\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mindigo\Auth\Models\User;
use Mindigo\StudentDiscussion\Http\Requests\StoreDiscussionMessageRequest;
use Mindigo\TeacherDiscussion\Http\Requests\UpdateDiscussionPreferenceRequest;
use Mindigo\TeacherDiscussion\Http\Requests\UpdatePinnedMessageRequest;
use Mindigo\TeacherDiscussion\Models\DiscussionAttachment;
use Mindigo\TeacherDiscussion\Models\DiscussionMessage;
use Mindigo\TeacherDiscussion\Models\DiscussionParticipant;
use Mindigo\TeacherDiscussion\Models\DiscussionThread;
use Mindigo\TeacherDiscussion\Services\TeacherDiscussionService;

class DiscussionController extends Controller
{
    public function __construct(protected TeacherDiscussionService $service) {}

    public function index(Request $request)
    {
        session()->forget('url.intended');

        /** @var User $student */
        $student = Auth::user();
        $this->service->ensureClassThreads($student);

        $selectedThread = $this->service->selectedThreadFor($student, $request->integer('thread') ?: null);
        if ($selectedThread) {
            $this->service->markAsRead($selectedThread, $student);
        }
        $threads = $this->service->threadsFor($student);
        $messages = $selectedThread ? $this->service->messages($selectedThread) : collect();
        $members = $selectedThread ? $this->service->members($selectedThread) : collect();
        $attachments = $selectedThread ? $this->service->attachments($selectedThread) : collect();
        $pinnedMessages = $selectedThread ? $this->service->pinnedMessages($selectedThread) : collect();
        $currentPreference = $selectedThread ? $this->service->preferenceFor($selectedThread, $student) : null;
        $candidateUsers = $this->service->candidateUsers($student);
        $addMemberCandidates = $selectedThread ? $this->service->candidateUsersFor($student, $selectedThread) : collect();
        $canManage = $selectedThread ? $this->service->canManageThread($selectedThread, $student) : false;
        $isOwner = $selectedThread ? $this->service->participantRole($selectedThread, (int) $student->getAuthIdentifier()) === DiscussionParticipant::ROLE_OWNER : false;
        $ownerUserIds = $selectedThread ? $this->service->ownerUserIds($selectedThread) : collect();
        $hasOlderMessages = $selectedThread && $messages->isNotEmpty() ? $this->service->hasOlderMessages($selectedThread, $messages->first()?->id) : false;

        $routes = [
            'index' => 'student.discussions.index',
            'store' => 'student.discussions.messages.store',
            'attachment' => 'student.discussions.attachments.show',
            'groups' => 'student.discussions.groups.store',
            'direct' => 'student.discussions.direct.store',
            'preferences' => 'student.discussions.preferences.update',
            'messagePin' => 'student.discussions.messages.pin',
            'messageUpdate' => 'student.discussions.messages.update',
            'messageDestroy' => 'student.discussions.messages.destroy',
            'messageReact' => 'student.discussions.messages.react',
            'messageOlder' => 'student.discussions.messages.older',
            'typing' => 'student.discussions.typing',
            'markAllRead' => 'student.discussions.mark-all-read',
            'membersStore' => 'student.discussions.members.store',
            'membersDestroy' => 'student.discussions.members.destroy',
            'memberRole' => 'student.discussions.members.role',
        ];

        return view('teacher-discussion::chat', compact('student', 'threads', 'selectedThread', 'messages', 'members', 'attachments', 'pinnedMessages', 'currentPreference', 'candidateUsers', 'addMemberCandidates', 'canManage', 'isOwner', 'ownerUserIds', 'hasOlderMessages', 'routes'));
    }

    public function store(StoreDiscussionMessageRequest $request, DiscussionThread $thread): RedirectResponse
    {
        $this->authorizeThread($thread);

        /** @var User $student */
        $student = Auth::user();
        $this->service->send($thread, $student, $request->input('body'), $request->file('attachments', []), $request->integer('reply_to_id') ?: null);

        return redirect()
            ->route('student.discussions.index', ['thread' => $thread->id])
            ->with('success', __('student-discussion::app.sent'));
    }

    /**
     * Tạo nhóm tuỳ chỉnh.
     */
    public function createGroup(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'theme_color' => ['nullable', 'string', 'max:20'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $thread = $this->service->createGroup(
            $user,
            $request->string('name')->toString(),
            $request->input('member_ids', []),
            $request->input('description'),
            $request->input('theme_color')
        );

        return redirect()
            ->route('student.discussions.index', ['thread' => $thread->id])
            ->with('success', __('student-discussion::app.group_created'));
    }

    /**
     * Bắt đầu hội thoại 1-1 với một người dùng khác.
     */
    public function findOrCreateDirect(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $other = User::findOrFail($request->integer('user_id'));

        abort_if((int) $other->getAuthIdentifier() === (int) $user->getAuthIdentifier(), 422);

        $thread = $this->service->findOrCreateDirect($user, $other);

        return redirect()
            ->route('student.discussions.index', ['thread' => $thread->id]);
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

    public function markAllAsRead(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->service->markAllAsRead($user);

        return back()->with('success', __('teacher-discussion::app.all_marked_read'));
    }

    public function updatePreferences(UpdateDiscussionPreferenceRequest $request, DiscussionThread $thread): RedirectResponse
    {
        $this->authorizeThread($thread);
        $this->service->updatePreferences($thread, $request->user(), $request->validated());

        return back()->with('success', __('teacher-discussion::app.preferences_updated'));
    }

    public function updateMessagePin(
        UpdatePinnedMessageRequest $request,
        DiscussionThread $thread,
        DiscussionMessage $message
    ): RedirectResponse {
        $this->authorizeThread($thread);
        $this->service->updateMessagePin($thread, $message, $request->user(), $request->boolean('is_pinned'));

        return back()->with('success', __('teacher-discussion::app.message_pin_updated'));
    }

    public function addMember(Request $request, DiscussionThread $thread): RedirectResponse
    {
        $this->authorizeThread($thread);
        $this->authorizeManage($thread);

        $request->validate([
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $this->service->addCandidateParticipants($thread, $request->user(), $request->input('member_ids'));

        return back()->with('success', __('teacher-discussion::app.member_added'));
    }

    public function removeMember(DiscussionThread $thread, int $user): RedirectResponse
    {
        $this->authorizeThread($thread);
        $this->authorizeManage($thread);

        $this->service->removeParticipant($thread, $user);

        return back()->with('success', __('teacher-discussion::app.member_removed'));
    }

    public function updateMessage(Request $request, DiscussionThread $thread, DiscussionMessage $message): RedirectResponse
    {
        $this->authorizeThread($thread);

        $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $this->service->updateMessage($thread, $message, $request->user(), $request->input('body'));

        return back()->with('success', __('teacher-discussion::app.message_updated'));
    }

    public function deleteMessage(DiscussionThread $thread, DiscussionMessage $message): RedirectResponse
    {
        $this->authorizeThread($thread);

        /** @var User $user */
        $user = Auth::user();
        $this->service->deleteMessage($thread, $message, $user);

        return back()->with('success', __('teacher-discussion::app.message_deleted'));
    }

    public function reactToMessage(Request $request, DiscussionThread $thread, DiscussionMessage $message): RedirectResponse
    {
        $this->authorizeThread($thread);

        $request->validate([
            'emoji' => ['required', 'string', 'max:32'],
        ]);

        $this->service->reactToMessage($thread, $message, $request->user(), $request->input('emoji'));

        return back();
    }

    public function typing(DiscussionThread $thread)
    {
        $this->authorizeThread($thread);

        /** @var User $user */
        $user = Auth::user();
        $this->service->broadcastTyping($thread, $user);

        return response()->noContent();
    }

    public function olderMessages(Request $request, DiscussionThread $thread)
    {
        $this->authorizeThread($thread);

        $beforeId = $request->integer('before_id');
        if (! $beforeId) {
            return response()->json(['messages' => [], 'has_more' => false]);
        }

        $messages = $this->service->olderMessages($thread, $beforeId);
        $oldestId = $messages->first()?->id;

        return response()->json([
            'messages' => $messages->map(fn (DiscussionMessage $message) => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'body' => $message->body,
                'created_at' => $message->created_at?->toIso8601String(),
                'is_pinned' => $message->is_pinned,
                'edited_at' => $message->edited_at?->toIso8601String(),
                'sender' => ['name' => $message->sender?->name],
                'attachments' => $message->attachments->map(fn ($a) => [
                    'id' => $a->id,
                    'original_name' => $a->original_name,
                    'mime_type' => $a->mime_type,
                    'size_label' => $a->sizeLabel(),
                    'is_image' => $a->isImage(),
                ])->values()->all(),
                'replies_to' => $message->repliesTo ? [
                    'id' => $message->repliesTo->id,
                    'sender_name' => $message->repliesTo->sender?->name,
                    'body' => $message->repliesTo->body,
                ] : null,
                'reactions' => $message->reactionSummary(),
            ])->values()->all(),
            'has_more' => $this->service->hasOlderMessages($thread, $oldestId),
        ]);
    }

    public function updateMemberRole(Request $request, DiscussionThread $thread, int $user): RedirectResponse
    {
        $this->authorizeThread($thread);

        $request->validate([
            'role' => ['required', 'in:owner,admin,member'],
        ]);

        $this->service->changeRole($thread, $request->user(), $user, $request->input('role'));

        return back()->with('success', __('teacher-discussion::app.role_updated'));
    }

    private function authorizeThread(DiscussionThread $thread): void
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user instanceof User && $this->service->canAccess($thread, $user), 403);
    }

    private function authorizeManage(DiscussionThread $thread): void
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user instanceof User && $this->service->canManageThread($thread, $user), 403);
    }
}
