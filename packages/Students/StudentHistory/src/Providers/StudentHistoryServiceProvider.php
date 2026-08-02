<?php

namespace Mindigo\StudentHistory\Providers;

use Illuminate\Support\ServiceProvider;

class StudentHistoryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'student-history');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'student-history');
    }
}
