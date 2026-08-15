<?php

namespace Mindigo\TeacherDiscussion\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherDiscussion\Events\MessageDeleted;
use Mindigo\TeacherDiscussion\Events\MessageReacted;
use Mindigo\TeacherDiscussion\Events\MessageSent;
use Mindigo\TeacherDiscussion\Events\MessageTyping;
use Mindigo\TeacherDiscussion\Events\MessageUpdated;
use Mindigo\TeacherDiscussion\Models\DiscussionAttachment;
use Mindigo\TeacherDiscussion\Models\DiscussionMessage;
use Mindigo\TeacherDiscussion\Models\DiscussionMessageDeletion;
use Mindigo\TeacherDiscussion\Models\DiscussionParticipant;
use Mindigo\TeacherDiscussion\Models\DiscussionThread;

class TeacherDiscussionService
{
    public function classrooms(User $teacher): Collection
    {
        return Classroom::query()
            ->where('teacher_id', $teacher->getAuthIdentifier())
            ->where('status', 'active')
            ->withCount('students')
            ->orderBy('name')
            ->get();
    }

    /**
     * Danh sách hội thoại mà người dùng là thành viên (bất kỳ vai trò nào).
     */
    public function threadsFor(User $user): Collection
    {
        return DiscussionThread::query()
            ->join('teacher_discussion_participants as current_membership', function ($join) use ($user): void {
                $join->on('current_membership.thread_id', '=', 'teacher_discussion_threads.id')
                    ->where('current_membership.user_id', $user->getAuthIdentifier());
            })
            ->select('teacher_discussion_threads.*')
            ->addSelect([
                'viewer_is_muted' => 'current_membership.is_muted',
                'viewer_is_pinned' => 'current_membership.is_pinned',
                'unread_messages_count' => DiscussionMessage::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('teacher_discussion_messages.thread_id', 'teacher_discussion_threads.id')
                    ->whereNull('teacher_discussion_messages.deleted_at')
                    ->where('teacher_discussion_messages.sender_id', '!=', $user->getAuthIdentifier())
                    ->where(function (Builder $query): void {
                        $query->whereNull('current_membership.last_read_at')
                            ->orWhereColumn('teacher_discussion_messages.created_at', '>', 'current_membership.last_read_at');
                    }),
            ])
            ->with([
                'classroom' => fn ($query) => $query->select('id', 'name', 'code')->withCount('students'),
                'latestMessage',
                'participants.user:id,name,email,role,avatar',
            ])
            ->orderByDesc('current_membership.is_pinned')
            ->orderByDesc('current_membership.pinned_at')
            ->orderByDesc('teacher_discussion_threads.last_message_at')
            ->orderByDesc('teacher_discussion_threads.updated_at')
            ->get();
    }

    public function threads(User $teacher): Collection
    {
        return $this->threadsFor($teacher);
    }

    /**
     * Tự tạo thread cho mỗi lớp học của giáo viên + đăng ký owner participant.
     */
    public function ensureClassThreads(User $teacher): void
    {
        $this->classrooms($teacher)->each(function (Classroom $classroom) use ($teacher): void {
            $thread = DiscussionThread::firstOrCreate(
                ['teacher_id' => $teacher->getAuthIdentifier(), 'classroom_id' => $classroom->id],
                [
                    'type' => DiscussionThread::TYPE_CLASS,
                    'created_by' => $teacher->getAuthIdentifier(),
                    'last_message_at' => now(),
                ]
            );

            $this->syncClassParticipants($thread, $classroom);
        });
    }

    public function ensureThreadsForClassrooms(User $teacher): void
    {
        $this->ensureClassThreads($teacher);
    }

    /**
     * Đồng bộ participants từ học sinh active của lớp (owner = giáo viên).
     */
    public function syncClassParticipants(DiscussionThread $thread, Classroom $classroom): void
    {
        $teacherId = $classroom->teacher_id;
        $this->syncParticipant($thread, $teacherId, DiscussionParticipant::ROLE_OWNER);

        $classroom->students()
            ->where('classroom_students.status', 'active')
            ->pluck('users.id')
            ->each(function (int $studentId) use ($thread): void {
                $this->syncParticipant($thread, $studentId, DiscussionParticipant::ROLE_MEMBER);
            });
    }

    public function selectedThreadFor(User $user, ?int $threadId = null): ?DiscussionThread
    {
        $query = DiscussionThread::query()
            ->whereHas('participants', fn (Builder $query) => $query->where('user_id', $user->getAuthIdentifier()))
            ->with([
                'classroom' => fn ($query) => $query->select('id', 'name', 'code')->withCount('students'),
                'participants.user:id,name,email,role,avatar',
            ]);

        if ($threadId) {
            return $query->whereKey($threadId)->firstOrFail();
        }

        return $query->orderByDesc('last_message_at')->orderBy('id')->first();
    }

    public function selectedThread(User $teacher, ?int $threadId = null): ?DiscussionThread
    {
        return $this->selectedThreadFor($teacher, $threadId);
    }

    public function canAccess(DiscussionThread $thread, User $user): bool
    {
        return $user->isAdmin() || $this->isParticipant($thread, (int) $user->getAuthIdentifier());
    }

    public function isParticipant(DiscussionThread $thread, int $userId): bool
    {
        return $thread->participants()->where('user_id', $userId)->exists();
    }

    public function participantRole(DiscussionThread $thread, int $userId): ?string
    {
        return $thread->participants()->where('user_id', $userId)->value('role');
    }

    public function canManageThread(DiscussionThread $thread, User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $role = $this->participantRole($thread, (int) $user->getAuthIdentifier());

        return in_array($role, [DiscussionParticipant::ROLE_OWNER, DiscussionParticipant::ROLE_ADMIN], true);
    }

    public function ownerUserIds(DiscussionThread $thread): Collection
    {
        return $thread->participants()
            ->where('role', DiscussionParticipant::ROLE_OWNER)
            ->pluck('user_id');
    }

    public function messages(DiscussionThread $thread, ?int $beforeId = null, int $limit = 60, ?int $viewerId = null): Collection
    {
        return $thread->messages()
            ->with($this->messageRelations())
            ->when($beforeId, fn (Builder $query) => $query->where('id', '<', $beforeId))
            ->when($viewerId, fn (Builder $query) => $query->notDeletedFor($viewerId))
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->sortBy('created_at')
            ->values();
    }

    /**
     * Cho biết còn tin nhắn cũ hơn một tin cụ thể không.
     */
    public function hasOlderMessages(DiscussionThread $thread, ?int $afterId = null, ?int $viewerId = null): bool
    {
        if (! $afterId) {
            return false;
        }

        return $thread->messages()
            ->where('id', '<', $afterId)
            ->when($viewerId, fn (Builder $query) => $query->notDeletedFor($viewerId))
            ->exists();
    }

    /**
     * Load các tin nhắn cũ hơn một tin cụ thể (dùng cho nút "xem tin cũ hơn").
     */
    public function olderMessages(DiscussionThread $thread, int $beforeId, int $limit = 40, ?int $viewerId = null): Collection
    {
        return $thread->messages()
            ->with($this->messageRelations())
            ->where('id', '<', $beforeId)
            ->when($viewerId, fn (Builder $query) => $query->notDeletedFor($viewerId))
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->sortBy('created_at')
            ->values();
    }

    private function messageRelations(): array
    {
        return [
            'sender:id,name,email,role,avatar',
            'attachments',
            'pinnedBy:id,name',
            'repliesTo:id,sender_id,body',
            'repliesTo.sender:id,name',
            'reactions.user:id,name,email,role,avatar',
            'reads:message_id,user_id,read_at',
        ];
    }

    public function preferenceFor(DiscussionThread $thread, User $user): DiscussionParticipant
    {
        return $thread->participants()->firstOrCreate(
            ['user_id' => $user->getAuthIdentifier()],
            ['role' => DiscussionParticipant::ROLE_MEMBER, 'joined_at' => now()]
        );
    }

    public function updatePreferences(DiscussionThread $thread, User $user, array $preferences): DiscussionParticipant
    {
        return DB::transaction(function () use ($thread, $user, $preferences): DiscussionParticipant {
            $participant = $this->preferenceFor($thread, $user);

            if (array_key_exists('is_muted', $preferences)) {
                $participant->is_muted = (bool) $preferences['is_muted'];
            }

            if (array_key_exists('is_pinned', $preferences)) {
                $participant->is_pinned = (bool) $preferences['is_pinned'];
                $participant->pinned_at = $participant->is_pinned ? now() : null;
            }

            $participant->save();

            return $participant->refresh();
        });
    }

    public function updateMessagePin(
        DiscussionThread $thread,
        DiscussionMessage $message,
        User $user,
        bool $isPinned
    ): DiscussionMessage {
        abort_unless((int) $message->thread_id === (int) $thread->id, 404);
        abort_unless($this->canAccess($thread, $user), 403);

        return DB::transaction(function () use ($message, $user, $isPinned): DiscussionMessage {
            $lockedMessage = DiscussionMessage::query()->lockForUpdate()->findOrFail($message->id);
            $lockedMessage->forceFill([
                'is_pinned' => $isPinned,
                'pinned_at' => $isPinned ? now() : null,
                'pinned_by' => $isPinned ? $user->getAuthIdentifier() : null,
            ])->save();

            return $lockedMessage->refresh();
        });
    }

    public function attachments(DiscussionThread $thread): Collection
    {
        return DiscussionAttachment::query()
            ->whereHas('message', fn ($query) => $query->where('thread_id', $thread->id))
            ->latest('created_at')
            ->limit(24)
            ->get();
    }

    public function pinnedMessages(DiscussionThread $thread): Collection
    {
        return $thread->messages()
            ->where('is_pinned', true)
            ->with(['sender:id,name', 'pinnedBy:id,name'])
            ->latest('pinned_at')
            ->limit(20)
            ->get();
    }

    public function members(DiscussionThread $thread): Collection
    {
        return $thread->participants()
            ->with('user:id,name,email,role,avatar')
            ->get()
            ->map->user
            ->filter()
            ->values();
    }

    public function send(
        DiscussionThread $thread,
        User $sender,
        ?string $body,
        array $files = [],
        ?int $replyToId = null
    ): DiscussionMessage {
        $message = $thread->messages()->create([
            'sender_id' => $sender->getAuthIdentifier(),
            'reply_to_id' => $replyToId,
            'body' => trim((string) $body),
        ]);

        foreach ($files as $file) {
            $path = $file->store('teacher-discussions/'.$thread->id, 'public');

            $message->attachments()->create([
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize() ?: 0,
            ]);
        }

        $thread->forceFill(['last_message_at' => $message->created_at])->save();

        broadcast(new MessageSent($message->load('sender', 'attachments', 'repliesTo.sender', 'reactions.user')));

        return $message;
    }

    /**
     * Tìm hoặc tạo hội thoại 1-1 giữa hai người dùng.
     */
    public function findOrCreateDirect(User $owner, User $other): DiscussionThread
    {
        $otherId = (int) $other->getAuthIdentifier();
        $ownerId = (int) $owner->getAuthIdentifier();

        abort_unless(
            $this->candidateUsers($owner)->contains(fn (User $candidate) => (int) $candidate->getAuthIdentifier() === $otherId),
            403
        );

        $existing = DiscussionThread::query()
            ->where('type', DiscussionThread::TYPE_DIRECT)
            ->whereHas('participants', fn (Builder $query) => $query->whereIn('user_id', [$ownerId, $otherId]), '=', 2)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($owner, $ownerId, $other): DiscussionThread {
            $teacherId = $owner->isTeacher() ? $ownerId : ($other->isTeacher() ? $other->getAuthIdentifier() : $ownerId);

            $thread = DiscussionThread::create([
                'teacher_id' => $teacherId,
                'type' => DiscussionThread::TYPE_DIRECT,
                'name' => null,
                'created_by' => $ownerId,
                'last_message_at' => now(),
            ]);

            $this->syncParticipant($thread, $ownerId, DiscussionParticipant::ROLE_OWNER);
            $this->syncParticipant($thread, (int) $other->getAuthIdentifier(), DiscussionParticipant::ROLE_MEMBER);

            return $thread;
        });
    }

    /**
     * Tạo nhóm tuỳ chỉnh với danh sách thành viên.
     */
    public function createGroup(User $creator, string $name, array $memberIds, ?string $description = null, ?string $themeColor = null): DiscussionThread
    {
        $creatorId = (int) $creator->getAuthIdentifier();

        $thread = DB::transaction(function () use ($creatorId, $creator, $name, $memberIds, $description, $themeColor): DiscussionThread {
            $thread = DiscussionThread::create([
                'teacher_id' => $creator->isTeacher() ? $creatorId : null,
                'type' => DiscussionThread::TYPE_GROUP,
                'name' => $name,
                'description' => $description,
                'theme_color' => $themeColor,
                'created_by' => $creatorId,
                'last_message_at' => now(),
            ]);

            $this->syncParticipant($thread, $creatorId, DiscussionParticipant::ROLE_OWNER);

            foreach (array_unique($memberIds) as $memberId) {
                $this->syncParticipant($thread, (int) $memberId, DiscussionParticipant::ROLE_MEMBER);
            }

            return $thread;
        });

        return $thread->load('participants');
    }

    public function syncParticipant(DiscussionThread $thread, int $userId, string $role = DiscussionParticipant::ROLE_MEMBER): void
    {
        $thread->participants()->updateOrCreate(
            ['user_id' => $userId],
            ['role' => $role, 'joined_at' => now()]
        );
    }

    public function addParticipants(DiscussionThread $thread, array $userIds, string $role = DiscussionParticipant::ROLE_MEMBER): int
    {
        $added = 0;

        foreach (array_unique($userIds) as $userId) {
            $this->syncParticipant($thread, (int) $userId, $role);
            $added++;
        }

        return $added;
    }

    /**
     * Thêm thành viên với kiểm tra ứng viên hợp lệ theo hội thoại (chặn mời người ngoài vào group lớp).
     */
    public function addCandidateParticipants(DiscussionThread $thread, User $actor, array $userIds): int
    {
        $allowed = array_filter(
            array_unique($userIds),
            fn ($id) => $this->isCandidateFor($actor, $thread, (int) $id)
        );

        return $this->addParticipants($thread, $allowed);
    }

    public function removeParticipant(DiscussionThread $thread, int $userId): bool
    {
        $participant = $thread->participants()->where('user_id', $userId)->first();

        if (! $participant || $participant->isOwner()) {
            return false;
        }

        return (bool) $participant->delete();
    }

    public function updateRole(DiscussionThread $thread, int $userId, string $role): bool
    {
        if (! in_array($role, DiscussionParticipant::ROLES, true)) {
            return false;
        }

        $participant = $thread->participants()->where('user_id', $userId)->first();

        if (! $participant) {
            return false;
        }

        $participant->forceFill(['role' => $role])->save();

        return true;
    }

    /**
     * Đổi vai trò thành viên (chỉ owner; không hạ role của owner khác;
     * không bỏ vai trò owner cuối cùng của chính mình).
     */
    public function changeRole(DiscussionThread $thread, User $actor, int $userId, string $role): bool
    {
        abort_unless($this->canManageThread($thread, $actor), 403);

        if (! in_array($role, DiscussionParticipant::ROLES, true)) {
            abort(422, 'Invalid role');
        }

        $participant = $thread->participants()->where('user_id', $userId)->first();
        abort_unless($participant, 404);

        $isSelf = (int) $userId === (int) $actor->getAuthIdentifier();
        $isTargetOwner = $participant->isOwner();

        // Không thể hạ vai trò của owner khác.
        if ($isTargetOwner && ! $isSelf) {
            abort(403);
        }

        // Không thể hạ mình khỏi owner khi là owner duy nhất.
        if ($isSelf && $isTargetOwner && $role !== DiscussionParticipant::ROLE_OWNER) {
            $ownerCount = $thread->participants()->where('role', DiscussionParticipant::ROLE_OWNER)->count();
            abort_if($ownerCount <= 1, 422, 'Cannot demote the only owner');
        }

        return $this->updateRole($thread, $userId, $role);
    }

    /**
     * Cập nhật nội dung tin nhắn (chỉ người gửi, chỉ khi chưa ai khác đọc).
     */
    public function updateMessage(DiscussionThread $thread, DiscussionMessage $message, User $user, string $body): DiscussionMessage
    {
        abort_unless((int) $message->thread_id === (int) $thread->id, 404);
        abort_unless((int) $message->sender_id === (int) $user->getAuthIdentifier(), 403);

        // Không cho sửa khi đã có người khác đọc tin nhắn.
        abort_if($message->isReadByOthers(), 403, __('teacher-discussion::app.message_locked'));

        $message->forceFill([
            'body' => trim($body),
            'edited_at' => now(),
        ])->save();

        broadcast(new MessageUpdated($message->load('sender', 'attachments', 'repliesTo.sender', 'reactions.user')));

        return $message;
    }

    /**
     * Xoá tin nhắn theo 2 cơ chế:
     * - Nếu chưa ai khác đọc (thu hồi)   -> xoá cho tất cả mọi người.
     * - Nếu đã có người khác đọc          -> chỉ xoá phía người gửi (những người còn lại vẫn thấy).
     * Người quản lý/admin luôn xoá cho tất cả.
     *
     * @return string 'recall' (mọi người) hoặc 'self' (chỉ người gửi)
     */
    public function deleteMessage(DiscussionThread $thread, DiscussionMessage $message, User $user): string
    {
        abort_unless((int) $message->thread_id === (int) $thread->id, 404);

        $isSender = (int) $message->sender_id === (int) $user->getAuthIdentifier();
        $canManage = $this->canManageThread($thread, $user);

        abort_unless($isSender || $canManage, 403);

        // Quản lý xoá tin của người khác, hoặc người gửi chưa ai đọc -> thu hồi cho tất cả.
        if (! $isSender || ! $message->isReadByOthers()) {
            broadcast(new MessageDeleted($thread->id, $message->id));

            $message->delete();

            return 'recall';
        }

        // Đã có người khác đọc -> chỉ xoá phía người gửi.
        DiscussionMessageDeletion::query()
            ->updateOrCreate(
                ['message_id' => $message->id, 'user_id' => $user->getAuthIdentifier()],
                ['deleted_at' => now()]
            );

        return 'self';
    }

    /**
     * Thêm / gỡ phản ứng emoji của người dùng cho một tin nhắn.
     */
    public function reactToMessage(DiscussionThread $thread, DiscussionMessage $message, User $user, string $emoji): void
    {
        abort_unless((int) $message->thread_id === (int) $thread->id, 404);

        DB::transaction(function () use ($message, $user, $emoji): void {
            $existing = $message->reactions()
                ->where('user_id', $user->getAuthIdentifier())
                ->where('emoji', $emoji)
                ->first();

            if ($existing) {
                $existing->delete();
            } else {
                $message->reactions()->create([
                    'user_id' => $user->getAuthIdentifier(),
                    'emoji' => $emoji,
                ]);
            }
        });

        broadcast(new MessageReacted(
            $message->load('reactions.user:id,name,email,role,avatar'),
            $emoji,
            (int) $user->getAuthIdentifier()
        ));
    }

    /**
     * Thông báo trạng thái đang nhập cho các thành viên khác trong hội thoại.
     */
    public function broadcastTyping(DiscussionThread $thread, User $user): void
    {
        broadcast(new MessageTyping($thread, $user));
    }

    /**
     * Danh sách thành viên đã đọc tới thời điểm hiện tại của tin nhắn.
     */
    public function readReceipts(DiscussionThread $thread, DiscussionMessage $message): Collection
    {
        return $thread->participants()
            ->with('user:id,name,email,role,avatar')
            ->where('user_id', '!=', $message->sender_id)
            ->whereNotNull('last_read_at')
            ->whereColumn('last_read_at', '>=', 'teacher_discussion_messages.created_at')
            ->get()
            ->map->user
            ->filter()
            ->values();
    }

    /**
     * Đánh dấu hội thoại đã đọc tới hiện tại.
     * Ghi nhận read receipt từng tin nhắn (bulk insert) để hỗ trợ cơ chế thu hồi/sửa theo "đã xem".
     */
    public function markAsRead(DiscussionThread $thread, User $user): void
    {
        $userId = $user->getAuthIdentifier();

        $thread->participants()
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);

        $this->recordReads($thread, $userId);
    }

    /**
     * Bulk ghi read receipt cho tất cả tin nhắn của thread mà user chưa đọc
     * (tối ưu: 1 câu INSERT chọn từ DB, tránh vòng lặp N+1 và quá tải queue).
     */
    public function recordReads(DiscussionThread $thread, int $userId): void
    {
        $sub = DB::table('teacher_discussion_messages')
            ->select('id')
            ->selectRaw('? as user_id', [$userId])
            ->selectRaw('? as read_at', [now()])
            ->where('thread_id', $thread->id)
            ->where('sender_id', '!=', $userId)
            ->whereNull('deleted_at')
            ->whereNotIn('id', function ($query) use ($userId): void {
                $query->select('message_id')
                    ->from('teacher_discussion_message_reads')
                    ->where('user_id', $userId);
            });

        DB::table('teacher_discussion_message_reads')
            ->insertUsing(['message_id', 'user_id', 'read_at'], $sub);
    }

    public function markAllAsRead(User $user): void
    {
        DiscussionParticipant::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->update(['last_read_at' => now()]);

        $userId = $user->getAuthIdentifier();

        $sub = DB::table('teacher_discussion_messages')
            ->select('id')
            ->selectRaw('? as user_id', [$userId])
            ->selectRaw('? as read_at', [now()])
            ->where('sender_id', '!=', $userId)
            ->whereNull('deleted_at')
            ->whereNotIn('id', function ($query) use ($userId): void {
                $query->select('message_id')
                    ->from('teacher_discussion_message_reads')
                    ->where('user_id', $userId);
            });

        DB::table('teacher_discussion_message_reads')
            ->insertUsing(['message_id', 'user_id', 'read_at'], $sub);
    }

    public function unreadCountFor(User $user): int
    {
        return DiscussionThread::query()
            ->whereHas('participants', fn (Builder $query) => $query->where('user_id', $user->getAuthIdentifier()))
            ->get()
            ->sum(fn (DiscussionThread $thread) => $thread->unreadCountFor((int) $user->getAuthIdentifier()));
    }

    /**
     * Danh sách người dùng có thể mời vào hội thoại (tuỳ theo vai trò).
     */
    public function candidateUsers(User $user): Collection
    {
        $me = (int) $user->getAuthIdentifier();

        $query = User::query()
            ->whereKeyNot($me)
            ->where('is_active', true)
            ->select('id', 'name', 'email', 'role', 'avatar');

        // Admin thấy tất cả; teacher thấy học sinh; student thấy giáo viên + học sinh cùng lớp.
        if ($user->isAdmin()) {
            return $query->orderBy('name')->get();
        }

        if ($user->isTeacher()) {
            return $query->where('role', 'student')->orderBy('name')->get();
        }

        $peerIds = Classroom::query()
            ->whereHas('students', fn ($query) => $query
                ->where('student_id', $me)
                ->where('classroom_students.status', 'active'))
            ->get()
            ->flatMap(fn (Classroom $classroom) => $classroom->students()
                ->where('classroom_students.status', 'active')
                ->whereKeyNot($me)
                ->pluck('id'));

        return $query
            ->where(function (Builder $query) use ($peerIds) {
                $query->where('role', 'teacher')->orWhereIn('id', $peerIds);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Danh sách người dùng có thể mời thêm vào một hội thoại cụ thể.
     * Với thread lớp học, chỉ cho phép mời học sinh cùng lớp (active).
     */
    public function candidateUsersFor(User $user, DiscussionThread $thread): Collection
    {
        if ($thread->type === DiscussionThread::TYPE_CLASS && $thread->classroom) {
            return User::query()
                ->join('classroom_students', 'classroom_students.student_id', '=', 'users.id')
                ->where('classroom_students.classroom_id', $thread->classroom->id)
                ->where('classroom_students.status', 'active')
                ->where('users.is_active', true)
                ->select('users.id', 'users.name', 'users.email', 'users.role', 'users.avatar')
                ->orderBy('users.name')
                ->distinct()
                ->get();
        }

        return $this->candidateUsers($user);
    }

    /**
     * Kiểm tra user id có phải là ứng viên hợp lệ để mời vào hội thoại không.
     */
    public function isCandidateFor(User $user, DiscussionThread $thread, int $userId): bool
    {
        return $this->candidateUsersFor($user, $thread)
            ->contains(fn (User $candidate) => (int) $candidate->getAuthIdentifier() === $userId);
    }
}
