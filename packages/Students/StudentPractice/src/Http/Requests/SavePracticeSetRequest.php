<?php

namespace Mindigo\StudentPractice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\QuestionBank\Models\Question;

class SavePracticeSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStudent() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'subject' => ['nullable', 'string', 'max:120'],
            'topic' => ['nullable', 'string', 'max:180'],
            'difficulty' => ['nullable', Rule::in(Question::DIFFICULTIES)],
            'source' => ['required', Rule::in(['manual', 'weak_topics', 'mistakes'])],
            'skill_id' => ['nullable', 'integer', Rule::exists('practice_skills', 'id')->where('status', 'active')->whereNull('deleted_at')],
            'question_count' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }
}
