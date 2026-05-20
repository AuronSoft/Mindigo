<?php

namespace Mindigo\AuditLog\Providers;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Mindigo\AuditLog\Services\AuditLogService;

class AuditLogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Mindigo-audit-log');

        Event::listen(Login::class, [AuditLogService::class, 'recordLogin']);
        Event::listen(Logout::class, [AuditLogService::class, 'recordLogout']);
        Event::listen(Failed::class, [AuditLogService::class, 'recordFailedLogin']);
    }
}
