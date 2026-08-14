<?php

namespace Mindigo\StudentExam\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;

class SubmitSessionAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attempt = $this->route('attempt');

        return $attempt instanceof ExamSessionAttempt && (int) $attempt->user_id === (int) $this->user()?->getAuthIdentifier();
    }

    public function rules(): array
    {
        return ['answers' => ['nullable', 'array', 'max:500'], 'answers.*' => ['nullable'], 'answers.*.*' => ['nullable', 'string', 'max:20000']];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            /** @var ExamSessionAttempt $attempt */
            $attempt = $this->route('attempt');
            foreach ($this->input('answers', []) as $questionId => $answer) {
                if (! in_array((int) $questionId, $attempt->question_order, true)) {
                    $validator->errors()->add("answers.{$questionId}", __('student-exam::app.invalid_question'));
                }
                if (is_array($answer) && count($answer) > 100) {
                    $validator->errors()->add("answers.{$questionId}", __('student-exam::app.too_many_answers'));
                }
            }
        }];
    }
}
