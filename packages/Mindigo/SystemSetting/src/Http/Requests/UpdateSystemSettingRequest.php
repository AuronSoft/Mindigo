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
            'settings.required' => 'Vui lòng gửi dữ liệu cấu hình.',
            'settings.array' => 'Dữ liệu cấu hình không hợp lệ.',
            '*.required' => 'Vui lòng nhập đầy đủ cấu hình bắt buộc.',
            '*.email' => 'Email không hợp lệ.',
            '*.integer' => 'Giá trị phải là số nguyên.',
            '*.min' => 'Giá trị không được nhỏ hơn 0.',
            '*.max' => 'Giá trị vượt quá giới hạn cho phép.',
            '*.in' => 'Giá trị được chọn không hợp lệ.',
        ];
    }
}
