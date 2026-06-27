<?php

namespace Mindigo\StudentDiscussion\Providers;

use Illuminate\Support\ServiceProvider;

class StudentDiscussionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'student-discussion');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'student-discussion');
    }
}
