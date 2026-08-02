<?php

namespace Mindigo\StudentLiveSession\Providers;

use Illuminate\Support\ServiceProvider;

class StudentLiveSessionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'student-live-session');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'student-live-session');
    }
}
