<?php

namespace Mindigo\TeacherDiscussion\Services;

use Illuminate\Support\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\TeacherDiscussion\Models\DiscussionAttachment;
use Mindigo\TeacherDiscussion\Models\DiscussionMessage;
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

    public function threads(User $teacher): Collection
    {
        return DiscussionThread::query()
            ->where('teacher_id', $teacher->getAuthIdentifier())
            ->with([
                'classroom' => fn ($query) => $query->select('id', 'name', 'code')->withCount('students'),
                'latestMessage',
            ])
            ->withCount('messages')
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function ensureThreadsForClassrooms(User $teacher): void
    {
        $this->classrooms($teacher)->each(function (Classroom $classroom) use ($teacher): void {
            DiscussionThread::firstOrCreate([
                'teacher_id' => $teacher->getAuthIdentifier(),
                'classroom_id' => $classroom->id,
            ]);
        });
    }

    public function selectedThread(User $teacher, ?int $threadId = null): ?DiscussionThread
    {
        $query = DiscussionThread::query()
            ->where('teacher_id', $teacher->getAuthIdentifier())
            ->with(['classroom' => fn ($query) => $query->select('id', 'name', 'code')->withCount('students')]);

        if ($threadId) {
            return $query->whereKey($threadId)->firstOrFail();
        }

        return $query->orderByDesc('last_message_at')->orderBy('id')->first();
    }

    public function messages(DiscussionThread $thread): Collection
    {
        return $thread->messages()
            ->with(['sender:id,name,email,role', 'attachments'])
            ->oldest('created_at')
            ->limit(120)
            ->get();
    }

    public function attachments(DiscussionThread $thread): Collection
    {
        return DiscussionAttachment::query()
            ->whereHas('message', fn ($query) => $query->where('thread_id', $thread->id))
            ->latest('created_at')
            ->limit(24)
            ->get();
    }

    public function members(DiscussionThread $thread): Collection
    {
        if (! $thread->classroom) {
            return collect();
        }

        return $thread->classroom->students()
            ->select('users.id', 'users.name', 'users.email', 'users.role')
            ->orderBy('users.name')
            ->get();
    }

    public function send(DiscussionThread $thread, User $sender, ?string $body, array $files = []): DiscussionMessage
    {
        $message = $thread->messages()->create([
            'sender_id' => $sender->getAuthIdentifier(),
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

        return $message;
    }
}
