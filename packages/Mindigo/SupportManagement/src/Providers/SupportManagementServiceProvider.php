<?php

namespace Mindigo\SupportManagement\Providers;

use Illuminate\Support\ServiceProvider;

class SupportManagementServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'Mindigo-support-management');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'Mindigo-support-management');
    }
}
