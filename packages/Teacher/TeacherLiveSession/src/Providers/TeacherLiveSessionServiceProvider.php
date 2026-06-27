<?php

namespace Mindigo\TeacherLiveSession\Providers;

use Illuminate\Support\ServiceProvider;

class TeacherLiveSessionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'teacher-live-session');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'teacher-live-session');
    }
}
