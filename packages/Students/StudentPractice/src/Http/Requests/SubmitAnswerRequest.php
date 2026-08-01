<?php

namespace Mindigo\StudentPractice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view', $this->route('attempt')) ?? false;
    }

    public function rules(): array
    {
        return [
            'question_id' => ['required', 'integer', 'exists:question_bank_questions,id'],
            'answer' => ['required', 'array', 'min:1'],
            'answer.choice' => ['nullable', 'string', 'max:255'],
            'answer.choices' => ['nullable', 'array', 'max:20'],
            'answer.choices.*' => ['string', 'max:255', 'distinct'],
            'answer.answer' => ['nullable', 'boolean'],
            'answer.text' => ['nullable', 'string', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'question_id.required' => __('student-practice::app.validation.question_required'),
            'question_id.exists' => __('student-practice::app.validation.question_invalid'),
            'answer.required' => __('student-practice::app.validation.answer_required'),
            'answer.array' => __('student-practice::app.validation.answer_invalid'),
        ];
    }

    /**
     * Lấy câu trả lời của học sinh
     */
    public function answer(): array
    {
        return $this->input('answer', []);
    }
}
