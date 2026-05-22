<?php

namespace Mindigo\ClassroomManagement\Providers;

use Illuminate\Support\ServiceProvider;

class ClassroomManagementServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Mindigo-classroom-management');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'Mindigo-classroom-management');
    }
}
