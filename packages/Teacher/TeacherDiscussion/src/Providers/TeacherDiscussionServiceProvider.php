<?php

namespace Mindigo\TeacherDiscussion\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Mindigo\TeacherDiscussion\Events\MessageSent;
use Mindigo\TeacherDiscussion\Listeners\NotifyThreadParticipants;

class TeacherDiscussionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'teacher-discussion');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'teacher-discussion');

        Event::listen(MessageSent::class, NotifyThreadParticipants::class);
    }
}
