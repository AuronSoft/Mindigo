<?php

namespace Mindigo\RolePermission\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RolePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('role-permissions.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['nullable', 'array'],
        ];
    }

    public function submittedPermissions(): array
    {
        return $this->validated('permissions', []);
    }
}
