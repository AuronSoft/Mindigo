<?php

namespace Mindigo\StudentPractice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\QuestionBank\Models\Question;

class PracticeIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['student', 'admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:120'],
            'subject' => ['nullable', 'string', 'max:120'],
            'topic' => ['nullable', 'string', 'max:180'],
            'type' => ['nullable', 'string', Rule::in(Question::TYPES)],
            'difficulty' => ['nullable', 'string', Rule::in(Question::DIFFICULTIES)],
            'skill_id' => [
                'nullable', 'integer',
                Rule::exists('practice_skills', 'id')->where('status', 'active')->whereNull('deleted_at'),
            ],
        ];
    }
}
