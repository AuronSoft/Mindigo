<?php

namespace Mindigo\UserManagement\Services;

use Mindigo\Auth\Models\User;

class UserManagementService
{
    public function filteredList(array $filters)
    {
        $query = User::query()->latest('updated_at');

        if (filled($filters['keyword'] ?? null)) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        if (filled($filters['role'] ?? null)) {
            $query->where('role', $filters['role']);
        }

        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        } elseif (($filters['status'] ?? null) === 'deleted') {
            $query->onlyTrashed();
        }

        return $query->paginate(12)->withQueryString();
    }

    public function create(array $data): User
    {
        $user = User::query()->create($data);
        $this->audit('create', [], $user->only($this->auditFields()), $user);

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $oldValues = $user->only($this->auditFields());
        $user->fill($data)->save();
        $this->audit('update', $oldValues, $user->only($this->auditFields()), $user);

        return $user;
    }

    public function delete(User $user): void
    {
        $oldValues = $user->only($this->auditFields());
        $user->delete();
        $this->audit('delete', $oldValues, [], $user);
    }

    public function restore(int $id): User
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        $this->audit('restore', [], $user->only($this->auditFields()), $user);

        return $user;
    }

    public function stats(): array
    {
        return [
            'total' => User::query()->count(),
            'active' => User::query()->where('is_active', true)->count(),
            'teachers' => User::query()->where('role', 'teacher')->count(),
            'students' => User::query()->where('role', 'student')->count(),
        ];
    }

    public function statuses(): array
    {
        return [
            'active' => __('Mindigo-user-management::app.statuses.active'),
            'inactive' => __('Mindigo-user-management::app.statuses.inactive'),
            'deleted' => __('Mindigo-user-management::app.statuses.deleted'),
        ];
    }

    public function can(User $user, string $permission): bool
    {
        return method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($permission);
    }

    private function auditFields(): array
    {
        return ['id', 'name', 'email', 'role', 'phone', 'gender', 'date_of_birth', 'is_active'];
    }

    private function audit(string $action, array $oldValues, array $newValues, User $user): void
    {
        if (!class_exists(\Mindigo\AuditLog\Services\AuditLogService::class)) {
            return;
        }

        app(\Mindigo\AuditLog\Services\AuditLogService::class)->record(
            $action,
            'users',
            $oldValues,
            $newValues,
            ['user_id' => $user->id, 'email' => $user->email],
            $user
        );
    }
}
