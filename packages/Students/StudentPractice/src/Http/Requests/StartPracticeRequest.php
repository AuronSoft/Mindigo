<?php

namespace Mindigo\StudentPractice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\QuestionBank\Models\Question;

class StartPracticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['student', 'admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'mode' => ['required', 'string', 'in:subject,topic,mixed'],
            'subject' => ['nullable', 'required_if:mode,subject,topic', 'string', 'max:120'],
            'topic' => ['nullable', 'required_if:mode,topic', 'string', 'max:180'],
            'difficulty' => ['nullable', 'string', 'in:'.implode(',', Question::DIFFICULTIES)],
            'question_count' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'mode.required' => __('student-practice::app.validation.mode_required'),
            'mode.in' => __('student-practice::app.validation.mode_invalid'),
            'difficulty.in' => __('student-practice::app.validation.difficulty_invalid'),
        ];
    }
}
