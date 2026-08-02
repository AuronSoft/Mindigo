<?php

namespace Mindigo\Notification\Notifications;

use Illuminate\Notifications\Notification;

class AssignmentGraded extends Notification
{
    public function __construct(
        public string $title,
        public float|int|null $score = null,
        public float|int|null $maxScore = null,
        public ?string $url = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'assignment_graded',
            'icon' => 'clipboard-check',
            'tone' => 'green',
            'title' => $this->title,
            'score' => $this->score,
            'max_score' => $this->maxScore,
            'url' => $this->url,
        ];
    }
}
