<?php

namespace Mindigo\StudentPractice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\QuestionBank\Models\Question;

class StartSkillPracticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view', $this->route('skill')) ?? false;
    }

    public function rules(): array
    {
        return [
            'question_count' => ['required', 'integer', 'min:1', 'max:50'],
            'difficulty' => ['nullable', 'string', Rule::in(Question::DIFFICULTIES)],
        ];
    }
}
