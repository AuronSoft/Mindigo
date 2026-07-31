<?php

namespace Mindigo\LearningTools\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LearningNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('learning-tools.use') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'content' => ['nullable', 'string', 'max:50000'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'subject_topic_id' => ['nullable', 'integer', 'exists:subject_topics,id'],
            'is_pinned' => ['nullable', 'boolean'],
        ];
    }
}
