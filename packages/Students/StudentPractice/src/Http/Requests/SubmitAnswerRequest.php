<?php

namespace Mindigo\StudentPractice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('student|admin');
    }

    public function rules(): array
    {
        return [
            'question_id' => ['required', 'integer', 'exists:question_bank_questions,id'],
            'answer' => ['required', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'question_id.required' => 'Question ID is required',
            'question_id.exists' => 'The question does not exist',
            'answer.required' => 'Please provide an answer',
            'answer.array' => 'Answer must be in correct format',
        ];
    }

    /**
     * Lấy câu trả lời của học sinh
     */
    public function getAnswer(): array
    {
        return $this->input('answer', []);
    }
}