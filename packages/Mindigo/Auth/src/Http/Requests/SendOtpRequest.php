<?php

namespace Mindigo\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:employees,email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Mindigo-auth::app.validation.email.required'),
            'email.email' => __('Mindigo-auth::app.validation.email.email'),
            'email.exists' => __('Mindigo-auth::app.validation.email.exists'),
        ];
    }
}
