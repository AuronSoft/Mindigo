<?php

namespace Mindigo\LearningTools\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LearningResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['teacher', 'admin'], true)
            && ($this->user()?->hasPermissionTo('learning-resources.manage') ?? false);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:100000'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'subject_topic_id' => ['nullable', 'integer', 'exists:subject_topics,id'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ];
    }
}
