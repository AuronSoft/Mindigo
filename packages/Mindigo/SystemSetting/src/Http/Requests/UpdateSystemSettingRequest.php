<?php

namespace Mindigo\SystemSetting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\SystemSetting\Services\SystemSettingService;

class UpdateSystemSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        $rules = ['settings' => ['required', 'array']];
        $definitions = app(SystemSettingService::class)->definitions();

        foreach ($definitions as $group) {
            foreach ($group['settings'] as $key => $setting) {
                $rules["settings.$key"] = match ($setting['type']) {
                    'boolean' => ['nullable', 'boolean'],
                    'integer' => ['required', 'integer', 'min:0', 'max:100000'],
                    'email' => ['nullable', 'email', 'max:255'],
                    'select' => ['required', Rule::in(array_keys($setting['options']))],
                    default => ['nullable', 'string', 'max:255'],
                };
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'settings.required' => __('Mindigo-system-setting::app.validation.settings_required'),
            'settings.array' => __('Mindigo-system-setting::app.validation.settings_array'),
            '*.required' => __('Mindigo-system-setting::app.validation.required'),
            '*.email' => __('Mindigo-system-setting::app.validation.email'),
            '*.integer' => __('Mindigo-system-setting::app.validation.integer'),
            '*.min' => __('Mindigo-system-setting::app.validation.min'),
            '*.max' => __('Mindigo-system-setting::app.validation.max'),
            '*.in' => __('Mindigo-system-setting::app.validation.in'),
        ];
    }
}
