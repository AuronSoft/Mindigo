<?php

namespace Mindigo\TeacherResult\Providers;

use Illuminate\Support\ServiceProvider;

class TeacherResultServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'teacher-result');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'teacher-result');
    }
}
