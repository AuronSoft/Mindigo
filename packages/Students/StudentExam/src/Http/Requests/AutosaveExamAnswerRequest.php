<?php

namespace Mindigo\StudentExam\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Illuminate\Validation\Validator;

class AutosaveExamAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ExamAttempt|null $attempt */
        $attempt = $this->route('attempt');

        return $attempt !== null
            && (int) $attempt->user_id === (int) $this->user()?->getAuthIdentifier();
    }

    public function rules(): array
    {
        return [
            'question_id' => ['required', 'integer'],
            'answer' => ['nullable'],
            'answer.*' => ['nullable', 'string', 'max:20000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $answer = $this->input('answer');
            if (! is_null($answer) && ! is_scalar($answer) && ! is_array($answer)) {
                $validator->errors()->add('answer', __('student-exam::app.invalid_answer'));
            }

            if (is_array($answer) && count($answer) > 100) {
                $validator->errors()->add('answer', __('student-exam::app.too_many_answers'));
            }
        });
    }
}
