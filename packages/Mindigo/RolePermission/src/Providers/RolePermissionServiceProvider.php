<?php

namespace Mindigo\RolePermission\Providers;

use Illuminate\Support\ServiceProvider;

class RolePermissionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Mindigo-role-permission');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'Mindigo-role-permission');
    }
}
