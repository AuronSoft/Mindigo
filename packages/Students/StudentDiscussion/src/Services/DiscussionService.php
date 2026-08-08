<?php

namespace Mindigo\StudentDiscussion\Services;

use Illuminate\Support\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\TeacherDiscussion\Models\DiscussionAttachment;
use Mindigo\TeacherDiscussion\Models\DiscussionMessage;
use Mindigo\TeacherDiscussion\Models\DiscussionThread;

class DiscussionService
{
    public function classroomIdsForStudent(int $studentId): Collection
    {
        return Classroom::query()
            ->whereHas('students', fn ($query) => $query
                ->where('student_id', $studentId)
                ->where('classroom_students.status', 'active'))
            ->pluck('id');
    }

    public function threads(User $student): Collection
    {
        $classroomIds = $this->classroomIdsForStudent((int) $student->getAuthIdentifier());

        if ($classroomIds->isEmpty()) {
            return collect();
        }

        return DiscussionThread::query()
            ->whereIn('classroom_id', $classroomIds)
            ->with([
                'classroom' => fn ($query) => $query->select('id', 'name', 'code')->withCount('students'),
                'latestMessage',
                'participants.user:id,name,email,role,avatar',
            ])
            ->withCount('messages')
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function selectedThread(User $student, ?int $threadId = null): ?DiscussionThread
    {
        $classroomIds = $this->classroomIdsForStudent((int) $student->getAuthIdentifier());

        if ($classroomIds->isEmpty()) {
            return null;
        }

        $query = DiscussionThread::query()
            ->whereIn('classroom_id', $classroomIds)
            ->with([
                'classroom' => fn ($query) => $query->select('id', 'name', 'code')->withCount('students'),
                'participants.user:id,name,email,role,avatar',
            ]);

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

    public function canAccess(DiscussionThread $thread, int $studentId): bool
    {
        return $this->classroomIdsForStudent($studentId)
            ->map(fn ($id) => (int) $id)
            ->contains((int) $thread->classroom_id);
    }

    public function send(DiscussionThread $thread, User $sender, ?string $body, array $files = []): DiscussionMessage
    {
        $message = $thread->messages()->create([
            'sender_id' => $sender->getAuthIdentifier(),
            'body' => trim((string) $body),
        ]);

        foreach ($files as $file) {
            $path = $file->store('student-discussions/'.$thread->id, 'public');

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
