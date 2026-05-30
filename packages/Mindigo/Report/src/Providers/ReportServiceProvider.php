<?php

namespace Mindigo\Report\Providers;

use Illuminate\Support\ServiceProvider;

class ReportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Mindigo-report');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'Mindigo-report');
    }
}
