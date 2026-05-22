<?php

namespace Mindigo\SubjectManagement\Providers;

use Illuminate\Support\ServiceProvider;

class SubjectManagementServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Mindigo-subject-management');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'Mindigo-subject-management');
    }
}
