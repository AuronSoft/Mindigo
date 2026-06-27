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
            'body' => ['nullable', 'required_without:attachments', 'string', 'max:2000'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,gif,pdf,txt,doc,docx,xls,xlsx,ppt,pptx,zip,rar'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required_without' => __('teacher-discussion::app.message_required'),
            'body.max' => __('teacher-discussion::app.message_max'),
            'attachments.max' => __('teacher-discussion::app.attachment_count_max'),
            'attachments.*.max' => __('teacher-discussion::app.attachment_size_max'),
            'attachments.*.mimes' => __('teacher-discussion::app.attachment_mimes'),
        ];
    }
}
