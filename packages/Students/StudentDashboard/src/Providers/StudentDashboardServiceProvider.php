<?php

namespace Mindigo\StudentDashboard\Providers;

use Illuminate\Support\ServiceProvider;

class StudentDashboardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'student-dashboard');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'student-dashboard');
    }
}
