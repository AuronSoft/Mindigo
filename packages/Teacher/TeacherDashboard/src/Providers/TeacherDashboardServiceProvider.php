<?php

namespace Mindigo\TeacherDashboard\Providers;

use Illuminate\Support\ServiceProvider;

class TeacherDashboardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'teacher-dashboard');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'teacher-dashboard');
    }
}
