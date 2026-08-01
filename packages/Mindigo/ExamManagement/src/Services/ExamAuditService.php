<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Database\Eloquent\Model;
use Mindigo\AuditLog\Services\AuditLogService;

class ExamAuditService
{
    public function record(string $action, string $module, array $oldValues, array $newValues, array $metadata, Model $model): void
    {
        if (! class_exists(AuditLogService::class)) {
            return;
        }

        app(AuditLogService::class)->record(
            $action,
            $module,
            $oldValues,
            $newValues,
            $metadata,
            $model
        );
    }
}
