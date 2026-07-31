<?php

namespace Mindigo\LearningTools\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PersonalizedPracticeSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('learning-tools.use') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'subject' => ['nullable', 'string', 'max:120'],
            'topic' => ['nullable', 'string', 'max:180'],
            'difficulty' => ['nullable', Rule::in(['easy', 'medium', 'hard'])],
            'source' => ['required', Rule::in(['manual', 'weak_topics', 'mistakes'])],
            'question_count' => ['required', 'integer', 'min:1', 'max:50'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
        ];
    }
}
