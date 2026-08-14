<?php

namespace Mindigo\StudentExam\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;

class AutosaveSessionAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attempt = $this->route('attempt');

        return $attempt instanceof ExamSessionAttempt && (int) $attempt->user_id === (int) $this->user()?->getAuthIdentifier();
    }

    public function rules(): array
    {
        return ['question_id' => ['required', 'integer'], 'answer' => ['nullable'], 'answer.*' => ['nullable', 'string', 'max:20000']];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            /** @var ExamSessionAttempt $attempt */
            $attempt = $this->route('attempt');
            if (! in_array($this->integer('question_id'), $attempt->question_order, true)) {
                $validator->errors()->add('question_id', __('student-exam::app.invalid_question'));
            }
            if (is_array($this->input('answer')) && count($this->input('answer')) > 100) {
                $validator->errors()->add('answer', __('student-exam::app.too_many_answers'));
            }
        }];
    }
}
