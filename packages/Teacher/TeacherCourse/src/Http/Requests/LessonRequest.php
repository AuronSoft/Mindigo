<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name'                   => ['required', 'string', 'max:255'],
            'description'            => ['nullable', 'string'],
            'content'                => ['nullable', 'string'],
            'video'                  => ['nullable', 'file', 'mimes:mp4,mov,avi,webm', 'max:512000'],
            'remove_video'           => ['nullable', 'boolean'],
            'attachments'            => ['nullable', 'array'],
            'attachments.*'          => ['file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:20480'],
            'remove_attachments'     => ['nullable', 'array'],
            'remove_attachments.*'   => ['string'],
            'assignment_id'          => ['nullable', 'integer', 'exists:assignments,id'],
            'prerequisite_lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
        ];
    }
}
