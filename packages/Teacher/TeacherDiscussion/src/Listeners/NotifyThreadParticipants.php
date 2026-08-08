<?php

namespace Mindigo\TeacherDiscussion\Listeners;

use Illuminate\Support\Facades\Notification as NotificationFacade;
use Mindigo\Notification\Notifications\DiscussionMessageSent;
use Mindigo\TeacherDiscussion\Events\MessageSent;

class NotifyThreadParticipants
{
    public function handle(MessageSent $event): void
    {
        $message = $event->message->loadMissing(['thread.participants.user', 'sender']);

        if (! $message->thread) {
            return;
        }

        $preview = mb_strimwidth((string) $message->body, 0, 120, '…');
        $preview = $preview !== '' ? $preview : '📎';

        $recipients = $message->thread->participants
            ->map->user
            ->filter()
            ->reject(fn ($user) => (int) $user->id === (int) $message->sender_id)
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        $students = $recipients->filter(fn ($user) => $user->isStudent());
        $teachers = $recipients->reject(fn ($user) => $user->isStudent());

        if ($students->isNotEmpty()) {
            NotificationFacade::send(
                $students,
                new DiscussionMessageSent(
                    threadId: (string) $message->thread_id,
                    sender: $message->sender?->name ?? '—',
                    preview: $preview,
                    url: route('student.discussions.index', ['thread' => $message->thread_id]),
                )
            );
        }

        if ($teachers->isNotEmpty()) {
            NotificationFacade::send(
                $teachers,
                new DiscussionMessageSent(
                    threadId: (string) $message->thread_id,
                    sender: $message->sender?->name ?? '—',
                    preview: $preview,
                    url: route('teacher.discussions.index', ['thread' => $message->thread_id]),
                )
            );
        }
    }
}
