<?php

namespace Mindigo\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notif_new_quiz' => ['nullable', 'boolean'],
            'notif_system_news' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'notif_new_quiz.boolean' => 'Cài đặt thông báo đề thi mới không hợp lệ.',
            'notif_system_news.boolean' => 'Cài đặt thông báo hệ thống không hợp lệ.',
        ];
    }
}
