<?php

namespace Mindigo\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Mindigo-auth::app.validation.email.required'),
            'email.email' => __('Mindigo-auth::app.validation.email.email'),
            'password.required' => __('Mindigo-auth::app.validation.password.required'),
            'password.min' => __('Mindigo-auth::app.validation.password.min'),
        ];
    }
}
