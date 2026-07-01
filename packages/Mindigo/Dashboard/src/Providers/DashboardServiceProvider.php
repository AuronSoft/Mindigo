<?php

namespace Mindigo\Dashboard\Providers;

use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Mindigo-dashboard');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang','Mindigo-dashboard');
    }
}
