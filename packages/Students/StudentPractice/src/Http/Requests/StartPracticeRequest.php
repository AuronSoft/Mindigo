<?php

namespace Mindigo\StudentPractice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\QuestionBank\Models\Question;

class StartPracticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('student|admin');
    }

    public function rules(): array
    {
        return [
            'mode' => ['required', 'string', 'in:subject,topic,mixed'],
            'subject' => ['nullable', 'string'],
            'topic' => ['nullable', 'string'],
            'difficulty' => ['nullable', 'string', 'in:' . implode(',', Question::DIFFICULTIES)],
        ];
    }

    public function messages(): array
    {
        return [
            'mode.required' => 'Please select a practice mode',
            'mode.in' => 'Invalid practice mode selected',
            'difficulty.in' => 'Invalid difficulty level selected',
        ];
    }
}
