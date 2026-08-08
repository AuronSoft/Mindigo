<?php

namespace Mindigo\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MindigoIdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'type' => ['required', 'in:magic_link,otp'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Mindigo-auth::app.validation.email.required'),
            'email.email' => __('Mindigo-auth::app.validation.email.email'),
            'type.in' => __('Mindigo-auth::app.validation.type.in'),
        ];
    }
}
