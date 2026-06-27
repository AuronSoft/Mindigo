<?php

namespace Mindigo\TeacherDiscussion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiscussionMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => __('teacher-discussion::app.message_required'),
            'body.max' => __('teacher-discussion::app.message_max'),
        ];
    }
}
