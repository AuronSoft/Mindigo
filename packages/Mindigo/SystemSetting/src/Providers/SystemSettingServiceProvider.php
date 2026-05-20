<?php

namespace Mindigo\SystemSetting\Providers;

use Illuminate\Support\ServiceProvider;

class SystemSettingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Mindigo-system-setting');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'Mindigo-system-setting');
    }
}
