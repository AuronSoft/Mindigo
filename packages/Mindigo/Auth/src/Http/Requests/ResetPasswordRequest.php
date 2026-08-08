<?php

namespace Mindigo\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:employees,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => __('Mindigo-auth::app.validation.email.exists'),
            'password.min' => __('Mindigo-auth::app.validation.password.min'),
            'password.confirmed' => __('Mindigo-auth::app.validation.password.confirmed'),
        ];
    }
}
