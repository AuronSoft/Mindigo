<?php

namespace Mindigo\UserManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\Auth\Models\User;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('user') ? 'users.update' : 'users.create';

        return $this->user()?->hasPermissionTo($permission) ?? false;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'max:255'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'phone' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', Rule::in(array_keys(User::GENDERS))],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:1000'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function userData(): array
    {
        $data = $this->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $data;
    }
}
