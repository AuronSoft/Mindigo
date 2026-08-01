<?php

namespace Mindigo\ExamManagement\Providers;

use Illuminate\Support\ServiceProvider;

class ExamManagementServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'Mindigo-exam-management');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'Mindigo-exam-management');
    }
}
