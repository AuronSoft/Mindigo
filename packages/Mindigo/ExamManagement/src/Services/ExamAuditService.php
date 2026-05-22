<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Database\Eloquent\Model;

class ExamAuditService
{
    public function record(string $action, string $module, array $oldValues, array $newValues, array $metadata, Model $model): void
    {
        if (!class_exists(\Mindigo\AuditLog\Services\AuditLogService::class)) {
            return;
        }

        app(\Mindigo\AuditLog\Services\AuditLogService::class)->record(
            $action,
            $module,
            $oldValues,
            $newValues,
            $metadata,
            $model
        );
    }
}
