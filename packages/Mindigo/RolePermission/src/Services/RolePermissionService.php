<?php

namespace Mindigo\RolePermission\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mindigo\RolePermission\Models\RolePermission;
use Throwable;

class RolePermissionService
{
    public const ADMIN = 'admin';
    public const TEACHER = 'teacher';
    public const STUDENT = 'student';

    public static function roleLabels(): array
    {
        return [
            self::ADMIN => __('Mindigo-role-permission::app.roles.admin'),
            self::TEACHER => __('Mindigo-role-permission::app.roles.teacher'),
            self::STUDENT => __('Mindigo-role-permission::app.roles.student'),
        ];
    }

    public static function roleDescriptions(): array
    {
        return [
            self::ADMIN => __('Mindigo-role-permission::app.role_descriptions.admin'),
            self::TEACHER => __('Mindigo-role-permission::app.role_descriptions.teacher'),
            self::STUDENT => __('Mindigo-role-permission::app.role_descriptions.student'),
        ];
    }

    public static function permissionGroups(): array
    {
        return [
            'platform' => [
                'label' => __('Mindigo-role-permission::app.groups.platform'),
                'permissions' => [
                    'dashboard.view' => __('Mindigo-role-permission::app.permissions.dashboard.view'),
                    'reports.view' => __('Mindigo-role-permission::app.permissions.reports.view'),
                ],
            ],
            'content' => [
                'label' => __('Mindigo-role-permission::app.groups.content'),
                'permissions' => [
                    'exams.view' => __('Mindigo-role-permission::app.permissions.exams.view'),
                    'exams.create' => __('Mindigo-role-permission::app.permissions.exams.create'),
                    'exams.update' => __('Mindigo-role-permission::app.permissions.exams.update'),
                    'exams.publish' => __('Mindigo-role-permission::app.permissions.exams.publish'),
                    'exams.delete' => __('Mindigo-role-permission::app.permissions.exams.delete'),
                    'questions.view' => __('Mindigo-role-permission::app.permissions.questions.view'),
                    'questions.create' => __('Mindigo-role-permission::app.permissions.questions.create'),
                    'questions.update' => __('Mindigo-role-permission::app.permissions.questions.update'),
                    'questions.review' => __('Mindigo-role-permission::app.permissions.questions.review'),
                    'questions.delete' => __('Mindigo-role-permission::app.permissions.questions.delete'),
                ],
            ],
            'learner' => [
                'label' => __('Mindigo-role-permission::app.groups.learner'),
                'permissions' => [
                    'exams.attempt' => __('Mindigo-role-permission::app.permissions.exams.attempt'),
                    'results.view' => __('Mindigo-role-permission::app.permissions.results.view'),
                ],
            ],
            'support' => [
                'label' => __('Mindigo-role-permission::app.groups.support'),
                'permissions' => [
                    'support-tickets.view' => __('Mindigo-role-permission::app.permissions.support_tickets.view'),
                    'support-tickets.create' => __('Mindigo-role-permission::app.permissions.support_tickets.create'),
                    'support-tickets.reply' => __('Mindigo-role-permission::app.permissions.support_tickets.reply'),
                    'support-tickets.manage' => __('Mindigo-role-permission::app.permissions.support_tickets.manage'),
                    'support-tickets.delete' => __('Mindigo-role-permission::app.permissions.support_tickets.delete'),
                ],
            ],
            'admin' => [
                'label' => __('Mindigo-role-permission::app.groups.admin'),
                'permissions' => [
                    'users.view' => __('Mindigo-role-permission::app.permissions.users.view'),
                    'users.create' => __('Mindigo-role-permission::app.permissions.users.create'),
                    'users.update' => __('Mindigo-role-permission::app.permissions.users.update'),
                    'users.delete' => __('Mindigo-role-permission::app.permissions.users.delete'),
                    'users.restore' => __('Mindigo-role-permission::app.permissions.users.restore'),
                    'system-settings.view' => __('Mindigo-role-permission::app.permissions.system_settings.view'),
                    'system-settings.update' => __('Mindigo-role-permission::app.permissions.system_settings.update'),
                    'audit-logs.view' => __('Mindigo-role-permission::app.permissions.audit_logs.view'),
                    'role-permissions.view' => __('Mindigo-role-permission::app.permissions.role_permissions.view'),
                ],
            ],
        ];
    }

    public static function permissionMap(): array
    {
        if (!self::tableIsReady()) {
            return self::defaultPermissionMap();
        }

        $rows = RolePermission::query()
            ->whereIn('role', array_keys(self::roleLabels()))
            ->whereIn('permission', array_keys(self::flatPermissions()))
            ->get(['role', 'permission', 'allowed']);

        if ($rows->isEmpty()) {
            return self::defaultPermissionMap();
        }

        $expectedRows = count(self::roleLabels()) * count(self::flatPermissions());
        if ($rows->count() < $expectedRows) {
            self::syncDefaults();

            $rows = RolePermission::query()
                ->whereIn('role', array_keys(self::roleLabels()))
                ->whereIn('permission', array_keys(self::flatPermissions()))
                ->get(['role', 'permission', 'allowed']);
        }

        $map = array_fill_keys(array_keys(self::roleLabels()), []);

        foreach ($rows as $row) {
            if ($row->allowed) {
                $map[$row->role][] = $row->permission;
            }
        }

        $map[self::ADMIN] = array_keys(self::flatPermissions());

        return $map;
    }

    public static function defaultPermissionMap(): array
    {
        $all = array_keys(self::flatPermissions());

        return [
            self::ADMIN => $all,
            self::TEACHER => [
                'dashboard.view',
                'reports.view',
                'exams.view',
                'exams.create',
                'exams.update',
                'questions.view',
                'questions.create',
                'questions.update',
                'questions.delete',
                'support-tickets.view',
                'support-tickets.create',
                'support-tickets.reply',
            ],
            self::STUDENT => [
                'exams.view',
                'exams.attempt',
                'results.view',
                'support-tickets.view',
                'support-tickets.create',
                'support-tickets.reply',
            ],
        ];
    }

    public static function flatPermissions(): array
    {
        $permissions = [];

        foreach (self::permissionGroups() as $groupKey => $group) {
            foreach ($group['permissions'] as $key => $label) {
                $permissions[$key] = [
                    'key' => $key,
                    'label' => $label,
                    'group' => $groupKey,
                    'group_label' => $group['label'],
                ];
            }
        }

        return $permissions;
    }

    public static function roleHasPermission(?string $role, string $permission): bool
    {
        if ($role === self::ADMIN) {
            return true;
        }

        return in_array($permission, self::permissionMap()[$role] ?? [], true);
    }

    public static function rolePermissionCount(string $role): int
    {
        if ($role === self::ADMIN) {
            return count(self::flatPermissions());
        }

        return count(self::permissionMap()[$role] ?? []);
    }

    public static function syncDefaults(): void
    {
        if (!self::tableIsReady()) {
            return;
        }

        $roles = array_keys(self::roleLabels());
        $permissions = array_keys(self::flatPermissions());
        $defaults = self::defaultPermissionMap();

        DB::transaction(function () use ($roles, $permissions, $defaults): void {
            RolePermission::query()
                ->whereNotIn('role', $roles)
                ->orWhereNotIn('permission', $permissions)
                ->delete();

            foreach ($roles as $role) {
                foreach ($permissions as $permission) {
                    RolePermission::query()->firstOrCreate(
                        ['role' => $role, 'permission' => $permission],
                        ['allowed' => in_array($permission, $defaults[$role] ?? [], true)]
                    );
                }
            }
        });
    }

    public static function updatePermissionMap(array $submittedPermissions): array
    {
        if (!self::tableIsReady()) {
            return self::defaultPermissionMap();
        }

        $roles = array_keys(self::roleLabels());
        $permissions = array_keys(self::flatPermissions());
        $updatedMap = [];

        foreach ($roles as $role) {
            $updatedMap[$role] = array_values(array_intersect(
                $permissions,
                array_keys(array_filter($submittedPermissions[$role] ?? []))
            ));
        }

        $updatedMap[self::ADMIN] = $permissions;

        DB::transaction(function () use ($roles, $permissions, $updatedMap): void {
            RolePermission::query()
                ->whereNotIn('role', $roles)
                ->orWhereNotIn('permission', $permissions)
                ->delete();

            foreach ($roles as $role) {
                foreach ($permissions as $permission) {
                    RolePermission::query()->updateOrCreate(
                        ['role' => $role, 'permission' => $permission],
                        ['allowed' => in_array($permission, $updatedMap[$role] ?? [], true)]
                    );
                }
            }
        });

        return $updatedMap;
    }

    private static function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('role_permissions');
        } catch (Throwable) {
            return false;
        }
    }
}
