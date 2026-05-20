<?php

namespace Mindigo\AuditLog\Services;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Mindigo\AuditLog\Models\AuditLog;
use Throwable;

class AuditLogService
{
    public function record(
        string $action,
        string $module,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = [],
        mixed $auditable = null,
        mixed $user = null,
        ?Request $request = null
    ): ?AuditLog {
        if (! $this->tableIsReady()) {
            return null;
        }

        $request ??= request();
        $user ??= Auth::user();

        try {
            return AuditLog::query()->create([
                'user_id' => $user?->getAuthIdentifier(),
                'user_name' => $user?->name,
                'user_email' => $user?->email,
                'action' => $action,
                'module' => $module,
                'auditable_type' => is_object($auditable) ? $auditable::class : null,
                'auditable_id' => is_object($auditable) && method_exists($auditable, 'getKey') ? $auditable->getKey() : null,
                'old_values' => $oldValues ?: null,
                'new_values' => $newValues ?: null,
                'metadata' => $metadata ?: null,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'url' => $request?->fullUrl(),
                'method' => $request?->method(),
                'route_name' => $request?->route()?->getName(),
            ]);
        } catch (Throwable) {
            return null;
        }
    }

    public function recordLogin(Login $event): void
    {
        $this->record('login', 'auth', metadata: ['remember' => $event->remember], user: $event->user);
    }

    public function recordLogout(Logout $event): void
    {
        $this->record('logout', 'auth', user: $event->user);
    }

    public function recordFailedLogin(Failed $event): void
    {
        $credentials = collect($event->credentials)
            ->only(['email', 'Mindigo_id'])
            ->filter()
            ->all();

        $this->record('login_failed', 'auth', metadata: ['credentials' => $credentials], user: $event->user);
    }

    private function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('audit_logs');
        } catch (Throwable) {
            return false;
        }
    }
}
