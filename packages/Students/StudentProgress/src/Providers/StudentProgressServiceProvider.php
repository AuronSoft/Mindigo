<?php

namespace Mindigo\StudentProgress\Providers;

use Illuminate\Support\ServiceProvider;

class StudentProgressServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'student-progress');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'student-progress');
    }
}
