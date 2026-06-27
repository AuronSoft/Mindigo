<?php

namespace Mindigo\TeacherDiscussion\Providers;

use Illuminate\Support\ServiceProvider;

class TeacherDiscussionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'teacher-discussion');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'teacher-discussion');
    }
}
